FROM nginx:1.25-alpine

# Remove default nginx configuration
RUN rm -f /etc/nginx/conf.d/default.conf

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf
COPY conf.d /etc/nginx/conf.d

# Create directory for SSL certificates (to be mounted as volume)
RUN mkdir -p /etc/nginx/ssl

# Create log directory
RUN mkdir -p /var/log/nginx

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]

