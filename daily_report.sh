#!/bin/bash
export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

BOT_TOKEN="8635449354:AAGd58MPrM5i7zsQOSS3WQS-64z2_TGzBNk"
CHAT_ID="1694189450"
SITE_URL="https://bonbonkw.com"

# Check status and response time
RESPONSE=$(curl -k -s -o /dev/null -w "%{http_code}:%{time_total}" --max-time 15 "${SITE_URL}")
HTTP_CODE=$(echo "${RESPONSE}" | cut -d':' -f1)
RESP_TIME=$(echo "${RESPONSE}" | cut -d':' -f2)

CURRENT_TIME=$(TZ='Asia/Damascus' date '+%Y-%m-%d %I:%M %p')
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}')
MEM_USAGE=$(free -m | awk 'NR==2{printf "%.1f%%", $3*100/$2 }')

if [ "${HTTP_CODE}" = "200" ]; then
    STATUS_TEXT="🟢 <b>حالة المتجر:</b> متصل ويعمل بكفاءة عالية (200 OK)"
else
    STATUS_TEXT="⚠️ <b>حالة المتجر:</b> كود الاستجابة (${HTTP_CODE})"
fi

# Check Docker containers
RUNNING_CONTAINERS=$(docker ps --format '{{.Names}}' | grep -c "bonbon_kids")
TOTAL_CONTAINERS=4
CONTAINER_STATUS="🟢 <b>الخدمات الأساسية:</b> ${RUNNING_CONTAINERS}/${TOTAL_CONTAINERS} حاويات قيد التشغيل"

MSG="🌅 <b>تقرير الحالة اليومي - متجر BonBon</b>

📅 <b>التوقيت:</b> ${CURRENT_TIME} (توقيت سوريا)
${STATUS_TEXT}
⚡ <b>سرعة الاستجابة:</b> ${RESP_TIME} ثانية
${CONTAINER_STATUS}
💾 <b>استهلاك الرام:</b> ${MEM_USAGE}
💽 <b>مساحة القرص:</b> ${DISK_USAGE} مستخدم

🛡️ <i>نظام الحماية الذاتية (Auto-Heal) نشط ويتابع الموقع على مدار الساعة.</i>"

curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/sendMessage" \
    -d "chat_id=${CHAT_ID}" \
    --data-urlencode "text=${MSG}" \
    -d "parse_mode=HTML" > /dev/null 2>&1