## 🧅 Tor Docker Container with Admin Panel 🚀

A comprehensive Docker container providing Tor services (bridge, relay, server) with hidden service support, DNS resolution, HTTP proxy, and a complete web-based admin panel.

## ✨ Features

- **Tor Services**: Bridge, Relay, and Server modes
- **Hidden Services**: Easy .onion site creation and management
- **DNS Resolution**: Unbound DNS resolver with .onion domain support
- **HTTP Proxy**: Privoxy for web traffic routing through Tor
- **Web Interface**: Nginx with PHP support
- **Admin Panel**: Complete web-based management interface
- **REST API**: JWT-authenticated API for automation

## 🚀 Quick Start

### Build Container

```shell
git clone "https://github.com/casjaysdevdocker/tor" "$HOME/Projects/github/casjaysdevdocker/tor"
cd "$HOME/Projects/github/casjaysdevdocker/tor"
docker build -t casjaysdevdocker/tor .
```

### Run Container

```shell
docker run -d \
  --name tor-container \
  -p 8080:80 \
  -p 9050:9050 \
  -p 9053:9053 \
  -p 8118:8118 \
  -e TOR_ADMIN_USER=admin \
  -e TOR_ADMIN_PASS=secure_password \
  -v "/var/lib/srv/$USER/docker/casjaysdevdocker/tor/latest/data:/data" \
  -v "/var/lib/srv/$USER/docker/casjaysdevdocker/tor/latest/config:/config" \
  casjaysdevdocker/tor
```

## 🔐 Admin Panel

Access the web-based admin panel at: `http://localhost:8080/admin/`

### Authentication

Set admin credentials via environment variables:

- `TOR_ADMIN_USER` (default: admin)
- `TOR_ADMIN_PASS` (default: torpass123)
- `TOR_JWT_SECRET` (optional: custom JWT secret)

### Features

- **Dashboard**: Service status monitoring and control
- **Configuration**: Edit Tor, Nginx, Unbound, Privoxy configs
- **Hidden Services**: Create and manage .onion sites
- **Logs**: Real-time log monitoring with auto-refresh
- **API Tokens**: Generate JWT tokens for programmatic access

## 🔗 REST API

### Authentication

```bash
# Get JWT token
curl -X POST http://localhost:8080/admin/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}'
```

### Service Management

```bash
# List all services
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/admin/api/services

# Get specific service status
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/admin/api/services/tor-server

# Restart a service
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/admin/api/services/tor-server/restart
```

### Hidden Services

```bash
# List hidden services
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/admin/api/hidden-services

# Create hidden service
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"myapp","port_mapping":"80 127.0.0.1:8080"}' \
  http://localhost:8080/admin/api/hidden-services
```

## 🌐 Network Ports

| Port        | Service | Description                |
| ----------- | ------- | -------------------------- |
| 80          | Nginx   | Web server and admin panel |
| 8118        | Privoxy | HTTP proxy                 |
| 9040        | Tor     | Transparent proxy          |
| 9050        | Tor     | SOCKS proxy                |
| 9053        | Unbound | DNS resolver               |
| 9080        | Tor     | HTTP tunnel                |
| 57000-57010 | Tor     | Bridge/Relay ports         |

## 📁 Directory Structure

- `/config` - Configuration files (persistent)
- `/data` - Data files and logs (persistent)
- `/data/logs` - Service logs
- `/data/tor/server/services` - Hidden service data
- `/data/htdocs` - Web content for hidden services

## ⚙️ Environment Variables

### Admin Panel

- `TOR_ADMIN_USER` - Admin username (default: admin)
- `TOR_ADMIN_PASS` - Admin password (default: torpass123)
- `TOR_JWT_SECRET` - JWT signing secret (auto-generated if not set)

### Tor Configuration

- `TOR_BRIDGE_ENABLED` - Enable bridge mode (default: yes)
- `TOR_RELAY_ENABLED` - Enable relay mode (default: yes)
- `TOR_HIDDEN_ENABLED` - Enable hidden services (default: yes)
- `TOR_DNS_ENABLED` - Enable DNS forwarding (default: yes)
- `TOR_DEBUG` - Enable debug logging (default: no)

### Service Ports

- `TOR_BRIDGE_PT_PORT` - Bridge transport port (default: 57003)
- `TOR_RELAY_PORT` - Relay transport port (default: 57000)
- `TOR_SERVER_ACCOUNT_MAX` - Monthly bandwidth limit (default: 250 GBytes)

## 🔧 Development

### Build with Custom Options

```shell
docker build \
  --build-arg BUILD_DATE=$(date +%Y%m%d%H%M) \
  --build-arg PHP_VERSION=84 \
  -t tor-custom .
```

## Authors  

📽 dockermgr: [Github](https://github.com/dockermgr) 📽  
🤖 casjay: [Github](https://github.com/casjay) [Docker](https://hub.docker.com/r/casjay) 🤖  
⛵ CasjaysDevDocker: [Github](https://github.com/casjaysdevdocker) [Docker](https://hub.docker.com/r/casjaysdevdocker) ⛵  
