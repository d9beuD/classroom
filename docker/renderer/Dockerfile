FROM node:24-bookworm-slim

# Chromium for Mermaid (through Puppeteer), from Debian rather than Puppeteer's own download.
RUN apt-get update \
    && apt-get install -y --no-install-recommends chromium fonts-noto-core \
    && rm -rf /var/lib/apt/lists/*

ENV NODE_ENV=production \
    PUPPETEER_SKIP_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --omit=dev
COPY server.js ./

USER node
EXPOSE 3000
CMD ["node", "server.js"]
