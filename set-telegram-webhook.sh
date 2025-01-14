
ENV_FILE=".env"

if [ ! -f "$ENV_FILE" ]; then
  echo "Error: .env file not found!"
  exit 1
fi

get_env_var() {
  grep "^$1=" "$ENV_FILE" | cut -d '=' -f2- | tr -d '"'
}

TELEGRAM_BOT_TOKEN=$(get_env_var "TELEGRAM_BOT_TOKEN")
TELEGRAM_WEBHOOK_URL=$(get_env_var "TELEGRAM_WEBHOOK_URL")

if [ -z "$TELEGRAM_BOT_TOKEN" ] || [ -z "$TELEGRAM_WEBHOOK_URL" ]; then
  echo "Error: TELEGRAM_BOT_TOKEN or TELEGRAM_WEBHOOK_URL is missing in .env file!"
  exit 1
fi

RESPONSE=$(curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/setWebhook" \
  -d "url=${TELEGRAM_WEBHOOK_URL}/api/webhook")

if echo "$RESPONSE" | grep -q '"ok":true'; then
  echo "Webhook set successfully."
else
  echo "Failed to set webhook: $RESPONSE"
fi
