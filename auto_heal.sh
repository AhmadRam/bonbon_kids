#!/bin/bash
export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

BOT_TOKEN="8635449354:AAGd58MPrM5i7zsQOSS3WQS-64z2_TGzBNk"
CHAT_ID="1694189450"
LOG_FILE="/opt/bonbon_kids/auto_heal.log"
SITE_URL="https://bonbonkw.com"

send_telegram() {
    local text="$1"
    curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/sendMessage" \
        -d "chat_id=${CHAT_ID}" \
        --data-urlencode "text=${text}" \
        -d "parse_mode=HTML" > /dev/null 2>&1
}

# Check site status with 15s timeout
HTTP_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" --max-time 15 "${SITE_URL}")

if [ "${HTTP_STATUS}" != "200" ]; then
    TIMESTAMP=$(TZ='Asia/Damascus' date '+%Y-%m-%d %I:%M:%S %p')
    echo "[${TIMESTAMP}] Site DOWN! Status: ${HTTP_STATUS}. Initiating auto-recovery..." >> "${LOG_FILE}"
    
    send_telegram "⚠️ <b>تنبيه طارئ:</b> موقع BonBon متوقف أو يواجه خطأ برقم (<b>${HTTP_STATUS}</b>).
جاري بدء الإنعاش التلقائي وتشغيل السيرفر والحاويات الآن..."

    # 1. Ensure Docker daemon is active
    if ! systemctl is-active --quiet docker; then
        echo "[${TIMESTAMP}] Docker daemon inactive, restarting..." >> "${LOG_FILE}"
        systemctl restart docker >> "${LOG_FILE}" 2>&1
        sleep 5
    fi

    # 2. Ensure Host Nginx is active
    if ! systemctl is-active --quiet nginx; then
        echo "[${TIMESTAMP}] Nginx service inactive, restarting..." >> "${LOG_FILE}"
        systemctl restart nginx >> "${LOG_FILE}" 2>&1
    fi

    # 3. Start & up all project containers
    cd /opt/bonbon_kids || exit 1
    docker compose up -d >> "${LOG_FILE}" 2>&1

    # 4. If containers were up but site returned 500/502, restart app and nginx containers
    docker compose restart app nginx >> "${LOG_FILE}" 2>&1

    # Wait for services to fully warm up
    sleep 20

    # Re-check status
    NEW_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" --max-time 15 "${SITE_URL}")
    echo "[${TIMESTAMP}] Status after auto-recovery: ${NEW_STATUS}" >> "${LOG_FILE}"

    if [ "${NEW_STATUS}" = "200" ]; then
        send_telegram "✅ <b>تم الإنعاش بنجاح:</b> تم تشغيل السيرفر وعاد الموقع للعمل بكفاءة (<b>200 OK</b>) بعد التدخل التلقائي!"
    else
        send_telegram "❌ <b>فشل الإنعاش التلقائي:</b> تم محاولة تشغيل كافة الخدمات ولكن الموقع ما زال لا يستجيب (كود: <b>${NEW_STATUS}</b>). يرجى تدخل الدعم الفني!"
    fi
fi