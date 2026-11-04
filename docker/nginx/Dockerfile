FROM nginx:alpine

# Salin konfigurasi server block Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Salin aset publik web ke dalam direktori root Nginx
COPY public /var/www/public

