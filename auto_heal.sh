#!/bin/bash
HTTP_STATUS=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 https://bonbonkw.com)

if [ "$HTTP_STATUS" != "200" ]; then
    MSG="%e2%9a%a0%ef%b8%8f *تنبيه طارئ:* موقع BonBon يواجه خطأ برقم $HTTP_STATUS. جاري محاولة إعادة تشغيل السيرفر..."
    curl -s -X POST "https://api.telegram.org/bot8635449354:AAGd58MPrM5i7zsQOSS3WQS-64z2_TGzBNk/sendMessage" -d "chat_id=1694189450" -d "text=$MSG" -d "parse_mode=Markdown" > /dev/null

    echo "$(date): Website returned HTTP $HTTP_STATUS. Attempting to restart containers..." >> /opt/bonbon_kids/auto_heal.log
    cd /opt/bonbon_kids
    docker compose restart
    
    sleep 15
    NEW_STATUS=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 https://bonbonkw.com)
    echo "$(date): Status after restart: $NEW_STATUS" >> /opt/bonbon_kids/auto_heal.log
    
    if [ "$NEW_STATUS" == "200" ]; then
        MSG2="%e2%9c%85 *تم الإصلاح:* السيرفر عاد للعمل بنجاح بعد إعادة التشغيل!"
        curl -s -X POST "https://api.telegram.org/bot8635449354:AAGd58MPrM5i7zsQOSS3WQS-64z2_TGzBNk/sendMessage" -d "chat_id=1694189450" -d "text=$MSG2" -d "parse_mode=Markdown" > /dev/null
    else
        MSG3="%e2%9d%8c *فشل الإصلاح:* السيرفر لا يزال لا يعمل (الحالة: $NEW_STATUS). يرجى تدخل المبرمج!"
        curl -s -X POST "https://api.telegram.org/bot8635449354:AAGd58MPrM5i7zsQOSS3WQS-64z2_TGzBNk/sendMessage" -d "chat_id=1694189450" -d "text=$MSG3" -d "parse_mode=Markdown" > /dev/null
    fi
fi
