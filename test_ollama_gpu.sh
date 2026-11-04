#!/bin/bash
curl -s -X POST http://ollama:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{"model":"qwen2.5:1.5b","prompt":"Apa itu fisika?","stream":false}'