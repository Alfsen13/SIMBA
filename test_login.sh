#!/bin/bash
# Get login page with CSRF token AND session cookie
curl -s -c /tmp/cookies.txt http://localhost:8000/login > /dev/null

# Extract CSRF token from the page (need to get it AFTER session is established)
TOKEN=$(curl -s -b /tmp/cookies.txt http://localhost:8000/login | grep -oP 'name="_token" value="\K[^"]+')
echo "Token: $TOKEN"

# Login - use the SAME cookie jar for both requests
curl -s -b /tmp/cookies.txt -c /tmp/cookies.txt -X POST http://localhost:8000/login \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d "email=budi@simba.test&password=password&_token=$TOKEN" > /dev/null

# Check if login succeeded
HTTP_CODE=$(curl -s -b /tmp/cookies.txt -o /dev/null -w '%{http_code}' http://localhost:8000/pelajar)
echo "Pelajar dashboard: $HTTP_CODE"

# Access AI Tutor page
HTTP_CODE=$(curl -s -b /tmp/cookies.txt -o /dev/null -w '%{http_code}' http://localhost:8000/pelajar/ai-tutor/kelas/10/pertemuan/1)
echo "AI Tutor page: $HTTP_CODE"