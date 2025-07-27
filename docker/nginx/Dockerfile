# Usa una imagen liviana de NGINX
FROM nginx:alpine

# Crea el directorio del proyecto
RUN mkdir -p /var/www/html/public

# Copia el código de tu proyecto al contenedor
COPY ./public /var/www/html/public

# Copia tu configuración personalizada de NGINX
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Ajusta los permisos para el usuario nginx
RUN chown -R nginx:nginx /var/www/html && chmod -R 755 /var/www/html