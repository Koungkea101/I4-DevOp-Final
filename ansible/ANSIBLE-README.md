# Laravel Kubernetes Deployment with Ansible

This directory contains Ansible playbooks for automating Laravel deployment and testing in a Kubernetes environment.

## Overview

The Ansible automation performs the following tasks on the Laravel web server pod:

1. **Git Operations**: Handles local changes and pulls latest code (when applicable)
2. **Dependency Management**: Installs/updates Composer and NPM dependencies
3. **Asset Building**: Builds frontend assets using NPM
4. **Testing**: Runs Laravel tests against SQLite database
5. **Database Backup**: Creates MySQL database backup
6. **Database Migration**: Applies pending migrations to production database

## Files Description

- `ansible-laravel-deployment.yml` - Main Ansible playbook for full deployment
- `ansible-laravel-container.yml` - Simplified playbook optimized for container environments
- `inventory.ini` - Ansible inventory file defining the pod connection
- `ansible.cfg` - Ansible configuration file
- `run-ansible-deployment.sh` - Automated script to run the deployment

## Prerequisites

### 1. Ansible Installation

Install Ansible on your local machine:

```bash
# Ubuntu/Debian
sudo apt update && sudo apt install ansible

# RHEL/CentOS/Fedora
sudo dnf install ansible

# macOS
brew install ansible

# Python pip
pip install ansible
```

### 2. Required Ansible Collections

```bash
ansible-galaxy collection install community.mysql
ansible-galaxy collection install ansible.posix
```

### 3. Laravel Application Deployed

Ensure your Laravel application is deployed in Kubernetes:

```bash
cd .. && ./k8s/deploy.sh
```

### 4. Verify Pod is Running

```bash
kubectl get pods -n laravel-app
kubectl get services -n laravel-app
```

## Usage

### Method 1: Automated Script (Recommended)

```bash
# Make script executable
chmod +x run-ansible-deployment.sh

# Run the automated deployment
./run-ansible-deployment.sh
```

### Method 2: Manual Ansible Execution

```bash
# Run the main deployment playbook
ansible-playbook -i inventory.ini ansible-laravel-deployment.yml -v

# Or run the container-optimized version
ansible-playbook -i inventory.ini ansible-laravel-container.yml -v
```

### Method 3: Test Connection First

```bash
# Test SSH connectivity to the pod
ansible laravel-pod -i inventory.ini -m ping

# Run a simple command
ansible laravel-pod -i inventory.ini -m shell -a "php artisan --version"
```

## Configuration

### SSH Connection

The playbooks connect to the Laravel pod via SSH on port 30022:

- **Host**: localhost
- **Port**: 30022
- **Username**: root
- **Password**: Hello@123

### Database Configuration

- **MySQL Host**: 127.0.0.1 (localhost within the pod)
- **MySQL Port**: 3306
- **Database**: koungkea-db
- **Username**: root
- **Password**: Hello@123

### Test Configuration

Tests run with SQLite configuration as defined in `phpunit.xml`:

- **Test Environment**: SQLite in-memory database
- **Production Environment**: MySQL database

## Playbook Features

### Git Operations
- Checks for local changes
- Stashes changes if necessary
- Attempts to pull latest code (when git repository is properly configured)

### Dependency Management
- Installs Composer dependencies with optimization
- Installs NPM dependencies
- Builds frontend assets

### Testing
- Clears Laravel caches before testing
- Runs tests against SQLite database (isolated from production)
- Continues deployment even if tests fail (configurable)

### Database Operations
- Creates timestamped MySQL backup
- Applies database migrations
- Optimizes Laravel for production

### Error Handling
- Comprehensive error checking
- Graceful failure handling
- Detailed logging and output

## Example Output

```
PLAY [Laravel Container Deployment and Testing] *****************************

TASK [Display connection information] ***************************************
ok: [laravel-webserver] => {
    "msg": "Connected to Laravel web server pod: laravel-webserver"
}

TASK [Install/Update Composer dependencies] *********************************
changed: [laravel-webserver]

TASK [Build npm assets] *****************************************************
changed: [laravel-webserver]

TASK [Run Laravel tests with SQLite] ****************************************
changed: [laravel-webserver]

TASK [Create MySQL database backup] *****************************************
changed: [laravel-webserver]

TASK [Display final deployment summary] *************************************
ok: [laravel-webserver] => {
    "msg": "================================\nDEPLOYMENT SUMMARY\n================================\nComposer Install: SUCCESS\nNPM Build: SUCCESS\nTests: PASSED\nDatabase Backup: SUCCESS\nMigration: SUCCESS\n================================"
}
```

## Accessing Backup Files

Database backups are stored inside the pod at `/tmp/mysql-backup/`. To download them:

```bash
# Get pod name
POD_NAME=$(kubectl get pod -n laravel-app -l app=laravel -o jsonpath='{.items[0].metadata.name}')

# Copy backup files to local machine
kubectl cp laravel-app/$POD_NAME:/tmp/mysql-backup/ ./backups/

# List backup files
ls -la ./backups/
```

## Troubleshooting

### SSH Connection Issues

```bash
# Test direct SSH connection
ssh root@localhost -p 30022

# Check if service is running
kubectl get service laravel-service -n laravel-app

# Check pod status
kubectl describe pod -n laravel-app
```

### Ansible Connection Issues

```bash
# Test Ansible connectivity
ansible laravel-pod -i inventory.ini -m ping -vvv

# Check inventory configuration
ansible-inventory -i inventory.ini --list
```

### Pod Access Issues

```bash
# Port forward for direct access
kubectl port-forward service/laravel-service -n laravel-app 2222:22

# Then update inventory.ini to use port 2222
```

### Database Connection Issues

```bash
# Test MySQL connection from within pod
kubectl exec -it deployment/laravel-deployment -n laravel-app -c laravel-webserver -- mysqladmin ping -h 127.0.0.1 -u root -pHello@123
```

## Security Considerations

⚠️ **Important**: This configuration uses default passwords for demonstration purposes.

For production use:
- Change default SSH password
- Use SSH keys instead of passwords
- Store sensitive data in Ansible Vault
- Use proper secret management

## Customization

### Modify Variables

Edit the playbook variables section:

```yaml
vars:
  app_path: /var/www/html
  mysql_host: 127.0.0.1
  mysql_database: your-database
  mysql_user: your-user
  mysql_password: "{{ vault_mysql_password }}"
```

### Add Custom Tasks

Extend the playbooks with additional tasks:

```yaml
- name: Your custom task
  shell: |
    cd {{ app_path }}
    php artisan your:command
```

### Skip Certain Operations

Use tags to skip operations:

```bash
ansible-playbook -i inventory.ini ansible-laravel-container.yml --skip-tags backup
```

## Integration with CI/CD

This Ansible playbook can be integrated into CI/CD pipelines:

```yaml
# Example GitHub Actions step
- name: Deploy with Ansible
  run: |
    ansible-playbook -i inventory.ini ansible-laravel-container.yml
```
