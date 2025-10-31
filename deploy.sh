#!/bin/bash
set -e

# Load environment variables
if [ ! -f .env ]; then
    echo "Error: .env file not found"
    echo "Please create .env from .env.example"
    exit 1
fi

source .env

# Validate required variables
if [ -z "$SSH_USER" ] || [ -z "$REPO_PATH" ]; then
    echo "Error: SSH_USER and REPO_PATH must be set in .env"
    exit 1
fi

echo "Pushing to origin..."
git push origin

echo "Pulling on server..."
ssh "$SSH_USER" "cd $REPO_PATH && git pull"

echo "Deployment complete!"
