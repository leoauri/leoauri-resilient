.PHONY: help build serve deploy clean test all

help:
	@echo "Available targets:"
	@echo "  make build   - Build the site (pug + scss)"
	@echo "  make serve   - Start local dev server on :8000"
	@echo "  make deploy  - Deploy to production"
	@echo "  make test    - Run pytest tests"
	@echo "  make clean   - Remove build directory"
	@echo "  make all     - Build and test"

build:
	node build.js

serve:
	@echo "Starting server at http://localhost:8000"
	@echo "Press Ctrl+C to stop"
	cd leoauri.com && uv run python -m http.server 8000

deploy:
	./deploy.sh

test:
	uv run pytest tests/

clean:
	rm -rf leoauri.com

all: build test

.DEFAULT_GOAL := help
