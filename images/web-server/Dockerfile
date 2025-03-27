FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY ./frontend ./

ARG API_URL
ARG FILESTORE_URL
ARG ICECAST_URL
ARG BASE_URL
ARG DEFAULT_POSTER
ARG MAX_FILESIZE
RUN echo "VUE_APP_API_URL=${API_URL}" > .env.production
RUN echo "VUE_APP_FILESTORE_URL=${FILESTORE_URL}" >> .env.production
RUN echo "VUE_APP_ICECAST_URL=${ICECAST_URL}" >> .env.production
RUN echo "VUE_APP_BASE_URL=${BASE_URL}" >> .env.production
RUN echo "VUE_APP_DEFAULT_POSTER=${DEFAULT_POSTER}" >> .env.production
RUN echo "VUE_APP_MAX_FILESIZE=${MAX_FILESIZE}" >> .env.production
RUN npm i && npm run build

FROM nginx:alpine AS base
COPY images/web-server/default.conf /etc/nginx/conf.d/default.conf
RUN mkdir /etc/nginx/locations/
ARG MAX_FILESIZE
RUN sed -i "s/:max_filesize:/${MAX_FILESIZE}/g" /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]

FROM base AS development
COPY images/web-server/nginx-api.conf \
  images/web-server/nginx-filestore.conf \
  images/web-server/nginx-metrics-dev.conf \
  images/web-server/nginx-scheduler-dev.conf \
  /etc/nginx/locations

FROM base AS chan
COPY images/web-server/nginx-api.conf \
  images/web-server/nginx-filestore.conf \
  /etc/nginx/locations
COPY --from=frontend-builder /app/dist /var/www/html

FROM chan AS production
COPY images/web-server/nginx-metrics.conf \
  images/web-server/nginx-scheduler.conf \
  /etc/nginx/locations
