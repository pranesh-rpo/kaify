<div align="center">

# Kaify

An open-source & self-hostable platform for deploying applications, databases, and services on your own servers.

A Heroku / Netlify / Vercel alternative.

</div>

## About

Kaify is an open-source & self-hostable Docker management platform. It helps you manage your servers, applications, and databases on your own hardware — you only need an SSH connection. You can manage VPS, Bare Metal, Raspberry PIs, and anything else.

Imagine having the ease of a cloud but with your own servers. That is **Kaify**.

No vendor lock-in. All configurations for your applications, databases, and services are saved to your server. If you decide to stop using Kaify, you can still manage your running resources.

## Features

- **Application Deployment** — Git-based deployments with automatic builds
- **Database Management** — PostgreSQL, MySQL, MongoDB, Redis, and more
- **Docker Compose Services** — Deploy any Docker Compose stack
- **Domain & SSL** — Automatic HTTPS with Let's Encrypt
- **Server Management** — Manage multiple remote servers via SSH
- **Team Collaboration** — Multi-tenant with role-based access control
- **Monitoring** — Real-time container metrics, logs, and health checks
- **Backups** — Automated database backups with configurable schedules
- **Auto-Updates** — Kaify keeps itself up to date automatically

## Requirements

- A **Linux server** (see [supported operating systems](#supported-operating-systems))
- Minimum **2 CPU cores**
- Minimum **2 GB RAM**
- Minimum **30 GB disk space** (20 GB free)
- **Root or sudo access**
- Supported architectures: **AMD64**, **ARM64**

> **Important:** Kaify installs on Linux servers only. macOS and Windows are not supported as host operating systems.

## Installation

SSH into your Linux server and run:

```bash
curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/install.sh | sudo bash
```

The installer will:

1. Install required packages (curl, wget, git, jq, openssl)
2. Configure OpenSSH server
3. Install and configure Docker
4. Set up Docker network address pools
5. Download Kaify configuration files
6. Generate environment variables and secrets
7. Set up SSH keys for localhost access
8. Pull and start all containers

Once complete, access Kaify at `http://<your-server-ip>:8000`.

### Install a Specific Version

```bash
curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/install.sh | sudo bash -s v4.0.0-beta.462
```

### Pre-configure Root User

Skip the initial setup screen by providing credentials during installation:

```bash
ROOT_USERNAME=admin \
ROOT_USER_EMAIL=admin@example.com \
ROOT_USER_PASSWORD=your-secure-password \
  curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/install.sh | sudo bash
```

### Custom Docker Network Pool

```bash
DOCKER_ADDRESS_POOL_BASE=172.16.0.0/12 \
DOCKER_ADDRESS_POOL_SIZE=24 \
  curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/install.sh | sudo bash
```

### Custom Registry URL

```bash
REGISTRY_URL=your-registry.example.com \
  curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/install.sh | sudo bash
```

### Disable Auto-Updates

```bash
AUTOUPDATE=false \
  curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/install.sh | sudo bash
```

## Configuration

All configuration is stored in `/data/kaify/source/.env`.

| Variable | Description | Default |
|---|---|---|
| `ROOT_USERNAME` | Root user username | *(set during first login)* |
| `ROOT_USER_EMAIL` | Root user email | *(set during first login)* |
| `ROOT_USER_PASSWORD` | Root user password | *(set during first login)* |
| `APP_PORT` | Port Kaify listens on | `8000` |
| `SOKETI_PORT` | WebSocket server port | `6001` |
| `DOCKER_ADDRESS_POOL_BASE` | Docker network pool base | `10.0.0.0/8` |
| `DOCKER_ADDRESS_POOL_SIZE` | Docker network pool size | `24` |
| `DOCKER_POOL_FORCE_OVERRIDE` | Force override existing Docker pool config | `false` |
| `AUTOUPDATE` | Enable auto-updates | `true` |
| `REGISTRY_URL` | Docker image registry URL | `ghcr.io` |

## Architecture

Kaify runs as a set of Docker containers:

| Container | Image | Description |
|---|---|---|
| `kaify` | `ghcr.io/pranesh-rpo/kaify` | Main application (Laravel + PHP) |
| `kaify-db` | `postgres:15-alpine` | PostgreSQL database |
| `kaify-redis` | `redis:7-alpine` | Redis cache and queue |
| `kaify-realtime` | `ghcr.io/pranesh-rpo/kaify-realtime` | Soketi WebSocket server |

### Data Directory

All persistent data is stored in `/data/kaify/`:

```
/data/kaify/
├── source/          # Configuration files (.env, docker-compose)
├── ssh/             # SSH keys for server connections
│   ├── keys/        # Private keys
│   └── mux/         # SSH multiplexing sockets
├── applications/    # Deployed application data
├── databases/       # Database storage
├── backups/         # Backup files
├── services/        # Service data
├── proxy/           # Proxy configuration
│   └── dynamic/     # Dynamic proxy rules
└── sentinel/        # Sentinel monitoring
```

## Upgrading

Kaify auto-updates by default. To manually upgrade:

```bash
sudo bash /data/kaify/source/upgrade.sh
```

Upgrade to a specific version:

```bash
sudo bash /data/kaify/source/upgrade.sh v4.0.0-beta.462
```

Upgrade logs are stored at `/data/kaify/source/upgrade-*.log`.

### Custom Docker Compose

To customize the Docker Compose configuration without losing changes on upgrades, create:

```
/data/kaify/source/docker-compose.custom.yml
```

This file is automatically merged during upgrades.

## Backup

Back up your environment file to a safe location **outside the server** (e.g., a password manager):

```bash
cat /data/kaify/source/.env
```

This file contains all secrets and credentials needed to restore your Kaify instance.

## Troubleshooting

### Check container status

```bash
docker ps -a | grep kaify
```

### View application logs

```bash
docker logs kaify
```

### Check container health

```bash
docker inspect --format='{{.State.Health.Status}}' kaify
```

### View installation logs

```bash
ls -la /data/kaify/source/installation-*.log
```

### View upgrade logs

```bash
ls -la /data/kaify/source/upgrade-*.log
```

### SSH connectivity issues

If Kaify cannot connect to servers:

1. Verify `PermitRootLogin` is set to `yes`, `without-password`, or `prohibit-password` in `/etc/ssh/sshd_config`
2. Check that SSH keys exist at `/data/kaify/ssh/keys/`
3. Restart the SSH service: `systemctl restart sshd`

### Docker installed via Snap

Kaify does not support Docker installed via Snap. Remove it first:

```bash
snap remove docker
```

Then re-run the installer.

## Supported Operating Systems

| Distribution | Notes |
|---|---|
| Ubuntu 20.04+ | Recommended |
| Debian 11+ | |
| CentOS 8+ | |
| Fedora | |
| RHEL / Rocky Linux / AlmaLinux | |
| Arch Linux | Including Manjaro, EndeavourOS, CachyOS |
| Alpine Linux | Including postmarketOS |
| openSUSE Leap / Tumbleweed | |
| SLES | |
| Amazon Linux 2023 | |
| Raspbian | For Raspberry Pi |

## License

Open source. See [LICENSE](LICENSE) for details.
