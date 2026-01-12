#!/bin/bash

# --- AYARLAR ---
THRESHOLD=90              # Yüzde olarak disk doluluk eşiği
LOG_PATH="/var/lib/docker/containers"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# --- Disk kullanımını kontrol et ---
USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//g')

if [ "$USAGE" -ge "$THRESHOLD" ]; then
    echo "$DATE - Disk kullanımı %$USAGE, log temizleme başlatılıyor..."

    # Docker container loglarını truncate et
    find "$LOG_PATH" -name "*.log" -type f -exec sh -c 'echo "" > "$1"' _ {} \;

    echo "$DATE - Tüm Docker logları temizlendi."
else
    echo "$DATE - Disk kullanımı %$USAGE, işlem yapılmadı."
fi

