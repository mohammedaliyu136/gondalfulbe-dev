#!/bin/bash
# ============================================================
# deploy.sh — push local changes to GitHub, then deploy to
# the Gondal ERP production server via SSH.
#
# Usage:
#   ./deploy.sh "your commit message"
#   ./deploy.sh          (uses auto-generated message)
#
# Requirements:
#   - SSH key added to agent: ssh-add ~/Downloads/gondal-ssh/id_rsa
#   - GitHub remote set: git remote -v
# ============================================================

set -e

# ── Config ────────────────────────────────────────────────
SSH_USER="gondalf2"
SSH_HOST="131.153.147.50"
SSH_KEY="$HOME/Downloads/gondal-ssh/id_rsa"
REMOTE_PATH="/home2/gondalf2/public_html/erp2.gondalfulbe.ng"
BRANCH="main"
# ──────────────────────────────────────────────────────────

COMMIT_MSG="${1:-"deploy: $(date '+%Y-%m-%d %H:%M')"}"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
step() { echo -e "\n${CYAN}▶ $1${NC}"; }
ok()   { echo -e "${GREEN}✔ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠ $1${NC}"; }
fail() { echo -e "${RED}✖ $1${NC}"; exit 1; }

# ── 0. Check SSH agent has the key ────────────────────────
step "Checking SSH key"
if ! ssh-add -l 2>/dev/null | grep -q "$(basename $SSH_KEY)"; then
  warn "Key not in agent — adding now"
  ssh-add "$SSH_KEY" || fail "Could not add SSH key"
fi
ok "SSH key ready"

# ── 1. Stage & commit local changes ───────────────────────
step "Staging changes"
cd "$(dirname "$0")"

if git diff --quiet && git diff --cached --quiet; then
  warn "Nothing to commit — skipping commit step"
else
  git add -A
  git commit -m "$COMMIT_MSG"
  ok "Committed: $COMMIT_MSG"
fi

# ── 2. Push to GitHub ─────────────────────────────────────
step "Pushing to GitHub ($BRANCH)"
git push origin "$BRANCH"
ok "Pushed to origin/$BRANCH"

DEPLOYED_SHA=$(git rev-parse --short HEAD)

# ── 3. Deploy on server ────────────────────────────────────
step "Deploying to $SSH_HOST"
ssh -o StrictHostKeyChecking=no -i "$SSH_KEY" "${SSH_USER}@${SSH_HOST}" bash << REMOTE
set -e
cd ${REMOTE_PATH}

echo "  → pulling from GitHub..."
git pull origin ${BRANCH}

echo "  → clearing caches..."
php artisan config:clear   --quiet
php artisan cache:clear    --quiet
php artisan view:clear     --quiet
php artisan route:clear    --quiet

echo "  → running migrations (if any)..."
php artisan migrate --force --quiet 2>/dev/null || true

echo "  → optimising..."
php artisan optimize --quiet 2>/dev/null || true

echo "  → done on server"
REMOTE

ok "Server is live at http://erp2.gondalfulbe.ng  (SHA: ${DEPLOYED_SHA})"

# ── 4. Log the deploy ─────────────────────────────────────
LOG_FILE="$(dirname "$0")/.deploy-log"
echo "$(date '+%Y-%m-%d %H:%M:%S') | SHA: ${DEPLOYED_SHA} | msg: ${COMMIT_MSG}" >> "$LOG_FILE"
ok "Logged → .deploy-log"

echo -e "\n${GREEN}Deploy complete!${NC}"
