#!/bin/bash

# Directories where uploads and metadata are stored
UPLOAD_DIR="/var/tmp/tus/uploads/"
META_DIR="/var/tmp/tus/meta/"

# 1 hour expiration time
EXPIRATION_TIME=3600

# Current timestamp
CURRENT_TIME=$(date +%s)

# Function to remove expired files
cleanup_expired_uploads() {
  for upload_file in "$UPLOAD_DIR"/*.part; do
    # Check if it's a regular file
    if [ -f "$upload_file" ]; then
      # Get the last modification time of the file
      MOD_TIME=$(stat -c %Y "$upload_file")
      
      # Calculate the age of the file
      FILE_AGE=$((CURRENT_TIME - MOD_TIME))
      
      # If the file is older than the expiration time, delete it
      if [ "$FILE_AGE" -ge "$EXPIRATION_TIME" ]; then
        FILE_NAME=$(basename "$upload_file")
        META_FILE="$META_DIR/$FILE_NAME.meta"

        
        echo "Removing expired upload: $FILE_NAME"
        rm -f "$upload_file"
        
        # Remove the associated metadata file if it exists
        if [ -f "$META_FILE" ]; then
          rm -f "$META_FILE"
          echo "Removed associated metadata: $META_FILE"
        fi
      fi
    fi
  done
}

# Run the cleanup function
cleanup_expired_uploads
