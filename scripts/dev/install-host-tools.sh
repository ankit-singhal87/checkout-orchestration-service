#!/usr/bin/env sh
set -eu

apt_packages="git make curl jq openssl nodejs npm glab docker.io docker-compose-v2"
brew_packages="git make curl jq openssl node glab docker docker-compose"
npm_packages="@openai/codex"

missing_commands=""

has_command() {
  command -v "$1" >/dev/null 2>&1
}

add_missing() {
  missing_commands="${missing_commands} $1"
}

sudo_cmd() {
  if [ "$(id -u)" -eq 0 ]; then
    "$@"
  elif has_command sudo; then
    sudo "$@"
  else
    echo "sudo is required to install host packages as a non-root user." >&2
    exit 1
  fi
}

install_apt_packages() {
  echo "Installing apt packages: $apt_packages"
  sudo_cmd apt-get update
  sudo_cmd apt-get install -y --no-install-recommends $apt_packages
}

install_brew_packages() {
  echo "Installing Homebrew packages: $brew_packages"
  brew update
  for package in $brew_packages; do
    if brew list "$package" >/dev/null 2>&1; then
      echo "Found Homebrew package: $package"
    else
      brew install "$package"
    fi
  done
}

install_codex() {
  if has_command codex; then
    echo "Found codex: $(command -v codex)"
    return
  fi

  if ! has_command npm; then
    echo "npm is required to install Codex CLI package(s): $npm_packages" >&2
    add_missing "npm"
    return
  fi

  echo "Installing Codex CLI package(s): $npm_packages"
  npm install -g $npm_packages
}

check_required_commands() {
  required_commands="git make curl jq openssl node npm glab docker codex"

  for tool in $required_commands; do
    if has_command "$tool"; then
      echo "Found $tool: $(command -v "$tool")"
    else
      add_missing "$tool"
    fi
  done

  if docker compose version >/dev/null 2>&1; then
    echo "Found Docker Compose plugin"
  elif has_command docker-compose; then
    echo "Found docker-compose: $(command -v docker-compose)"
  else
    add_missing "docker-compose"
  fi
}

ensure_docker_access() {
  if ! has_command docker; then
    return
  fi

  if docker info >/dev/null 2>&1; then
    echo "Docker daemon is reachable"
    return
  fi

  echo "Docker is installed, but the daemon is not reachable by this user." >&2
  echo "Start Docker and ensure the current user can access the Docker socket." >&2
}

if has_command apt-get; then
  install_apt_packages
elif has_command brew; then
  install_brew_packages
else
  echo "Unsupported package manager. Install these tools manually:" >&2
  echo "  git make curl jq openssl node npm glab docker docker-compose codex" >&2
  exit 1
fi

install_codex
check_required_commands
ensure_docker_access

if [ -n "$missing_commands" ]; then
  echo "Missing after install:$missing_commands" >&2
  exit 1
fi

echo "Essential host tools are installed."
echo "Authenticate separately where needed, for example: glab auth login"
