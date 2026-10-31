# SIMBA Laravel LMS - Multi-stage Docker build
# Base: FrankenPHP (PHP 8.3+) + Node.js + Python 3.14 + ML stack

# =============================================================================
# STAGE 1: Base FrankenPHP + Node.js + Python
# =============================================================================
FROM dunglas/frankenphp:1.4-php8.4 AS base

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libpq-dev \
    libssl-dev \
    pkg-config \
    curl \
    gnupg \
    ca-certificates \
    # Python 3.14 (from deadsnakes or source)
    software-properties-common \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 20 (LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install Python 3.11 (available in Debian Bookworm)
# Symlink as python3.14 for compatibility with DocumentExtractorService
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3.11 \
    python3.11-venv \
    python3.11-dev \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# Create python3.14 symlink for compatibility
RUN ln -sf /usr/bin/python3.11 /usr/local/bin/python3.14 \
    && ln -sf /usr/bin/python3.11 /usr/local/bin/python3 \
    && ln -sf /usr/bin/pip3 /usr/local/bin/pip

# Install Tesseract OCR
RUN apt-get update && apt-get install -y --no-install-recommends \
    tesseract-ocr \
    tesseract-ocr-ind \
    tesseract-ocr-eng \
    libtesseract-dev \
    poppler-utils \
    && rm -rf /var/lib/apt/lists/*

# Set tessdata path
ENV TESSDATA_PREFIX=/usr/share/tesseract-ocr/5/tessdata/

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    pdo_pgsql \
    zip \
    gd \
    intl \
    opcache \
    bcmath \
    pcntl \
    sockets

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# =============================================================================
# STAGE 2: Python ML Dependencies (separate layer for caching)
# =============================================================================
FROM base AS python-deps

# Install Python ML packages
# Note: PyTorch CUDA build needs specific index URL
# Debian 12 uses PEP 668 (externally managed), use --break-system-packages
RUN pip install --no-cache-dir --upgrade pip setuptools wheel --break-system-packages

# PyTorch with CUDA 12.6 (matching torch 2.9.1+cu126 from AGENTS.md)
RUN pip install --no-cache-dir \
    torch==2.9.1+cu126 \
    torchvision==0.24.1+cu126 \
    torchaudio==2.9.1+cu126 \
    --index-url https://download.pytorch.org/whl/cu126 \
    --break-system-packages

# PDF extraction dependencies
# pix2tex[gui] latest is 0.1.4 (not 0.1.7)
RUN pip install --no-cache-dir \
    pdfplumber \
    pymupdf \
    "pix2tex[gui]==0.1.4" \
    pillow \
    numpy \
    opencv-python-headless \
    --break-system-packages

# Other Python deps
# smalot.pdfparser is PHP package (installed via composer), not Python
RUN pip install --no-cache-dir \
    requests \
    python-dotenv \
    --break-system-packages

# =============================================================================
# STAGE 3: Application Build
# =============================================================================
FROM python-deps AS app

# Set working directory
WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock ./

# Copy artisan and minimal files needed for package:discover
COPY artisan ./
COPY bootstrap/app.php bootstrap/app.php

# Create bootstrap/cache directory for package:discover
RUN mkdir -p bootstrap/cache

# Update composer.lock for PHP 8.3 compatibility (regenerate lock file)
RUN rm -f composer.lock && composer update --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-req=php

# Install PHP dependencies (without dev for production, skip scripts for now)
ARG INSTALL_DEV=true
RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-req=php; \
    else \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts --ignore-platform-req=php; \
    fi

# Copy package.json for npm caching
COPY package.json package-lock.json* ./

# Install Node dependencies
RUN npm ci --ignore-scripts

# Copy application code
COPY . .

# Build frontend assets (skip package:discover during build to avoid cache corruption)
RUN npm run build

# =============================================================================
# STAGE 4: Production Image (FrankenPHP worker)
# =============================================================================
FROM app AS production

# Copy custom entrypoint
COPY docker-entrypoint.frankenphp.sh /usr/local/bin/docker-entrypoint.frankenphp.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.frankenphp.sh

# Copy Caddyfile for production
COPY Caddyfile /etc/caddy/Caddyfile

# Set production env
ENV APP_ENV=production \
    APP_DEBUG=false \
    SERVER_NAME=":8000" \
    PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d" \
    # Python path for DocumentExtractorService
    PYTHON_PATH="python3.14" \
    PDF_EXTRACT_TIMEOUT=600

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/up || exit 1

# Default command runs FrankenPHP with Caddyfile
ENTRYPOINT ["docker-entrypoint.frankenphp.sh"]

# =============================================================================
# STAGE 5: Development Image (with Vite HMR)
# =============================================================================
FROM app AS development

# Install dev tools
RUN composer install --no-interaction --prefer-dist

# Vite dev server port
EXPOSE 5173

# Override entrypoint for development
COPY docker-entrypoint.dev.sh /usr/local/bin/docker-entrypoint.dev.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.dev.sh

ENTRYPOINT ["docker-entrypoint.dev.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile.dev"]