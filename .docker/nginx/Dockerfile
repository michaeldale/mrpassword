FROM nginx:1.13-alpine

RUN rm -f /etc/nginx/conf.d/*

COPY etc/nginx/nginx.conf /etc/nginx/
COPY etc/nginx/conf.d/mrpassword.conf /etc/nginx/conf.d/
