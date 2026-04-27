#!/usr/bin/env sh
set -eu

remote="${REMOTE:-origin}"
main_branch="${MAIN_BRANCH:-main}"
main_ref="${MAIN_REF:-$remote/$main_branch}"

known_phase1_conflicts='
.github/workflows/mirror-validation.yml
.gitlab-ci.yml
README.md
apps/checkout/.env.example
apps/checkout/app/Application/Cart/AddCartItem.php
apps/checkout/app/Application/Cart/CartAddResult.php
apps/checkout/app/Application/Cart/CartReader.php
apps/checkout/app/Application/Catalog/CatalogReader.php
apps/checkout/app/Application/Tenant/TenantResolver.php
apps/checkout/app/Domain/Tenant/TenantContext.php
apps/checkout/app/Http/Controllers/Controller.php
apps/checkout/app/Http/Controllers/Web/CartController.php
apps/checkout/app/Http/Controllers/Web/CartItemController.php
apps/checkout/app/Http/Controllers/Web/ProductController.php
apps/checkout/app/Http/Controllers/Web/ShopController.php
apps/checkout/app/Http/Middleware/ResolveTenant.php
apps/checkout/app/Http/ViewModels/CartViewModel.php
apps/checkout/app/Http/ViewModels/CartViewModelFactory.php
apps/checkout/app/Http/ViewModels/ProductCardViewModel.php
apps/checkout/app/Http/ViewModels/ProductDetailViewModel.php
apps/checkout/app/Http/ViewModels/ProductViewModelFactory.php
apps/checkout/app/Http/ViewModels/ShopViewModel.php
apps/checkout/app/Infrastructure/Persistence/Eloquent/CartItemRecord.php
apps/checkout/app/Infrastructure/Persistence/Eloquent/CartRecord.php
apps/checkout/app/Infrastructure/Persistence/Eloquent/ProductRecord.php
apps/checkout/app/Infrastructure/Persistence/Eloquent/ProductVariantRecord.php
apps/checkout/app/Infrastructure/Persistence/Eloquent/TenantRecord.php
apps/checkout/app/Models/User.php
apps/checkout/app/Providers/AppServiceProvider.php
apps/checkout/bootstrap/app.php
apps/checkout/database/factories/UserFactory.php
apps/checkout/database/seeders/DatabaseSeeder.php
apps/checkout/database/seeders/DemoCatalogSeeder.php
apps/checkout/resources/views/cart/show.blade.php
apps/checkout/resources/views/components/layouts/shop.blade.php
apps/checkout/resources/views/product/show.blade.php
apps/checkout/resources/views/shop/index.blade.php
apps/checkout/routes/web.php
apps/checkout/tests/Feature/CartManagementTest.php
apps/checkout/tests/TestCase.php
docker-compose.yml
docs/api/openapi.checkout.yaml
wiki/architecture/README.md
docs/standards/php-8.5.md
docs/contracts/problem-details.md
wiki/status/phase-1-foundation.md
wiki/roadmap/checkout-mvp-plan.md
scripts/ci/validate-phase1.sh
scripts/dev/up.sh
scripts/test/checkout-app.sh
seed/fixtures/catalog-sample.json
'

fail() {
  echo "$1" >&2
  exit 1
}

is_known_phase1_conflict() {
  path="$1"
  printf '%s\n' "$known_phase1_conflicts" | grep -Fx "$path" >/dev/null 2>&1
}

if [ -f "$(git rev-parse --git-dir)/MERGE_HEAD" ]; then
  echo "Continuing in-progress merge."
  merge_status=1
else
  if [ -n "$(git status --porcelain)" ]; then
    fail "Working tree must be clean before merging $main_ref."
  fi

  git fetch "$remote" "$main_branch"

  if git merge --no-ff --no-commit "$main_ref"; then
    echo "Merged $main_ref without conflicts."
    merge_status=0
  else
    merge_status=1
  fi
fi

if [ "$merge_status" -ne 0 ]; then
  conflicts="$(git diff --name-only --diff-filter=U)"

  if [ -z "$conflicts" ]; then
    fail "Merge failed, but Git did not report unresolved file conflicts."
  fi

  echo "Merge conflicts:"
  printf '%s\n' "$conflicts"

  if [ "${RESOLVE_PHASE1_SCAFFOLD_CONFLICTS:-0}" != "1" ]; then
    cat >&2 <<EOF

Resolve conflicts manually, or rerun only for the known older-main Phase 1
scaffold overlap with:

  RESOLVE_PHASE1_SCAFFOLD_CONFLICTS=1 sh scripts/git/merge-main.sh

That mode keeps the current branch version for the known conflict set.
EOF
    exit 1
  fi

  for path in $conflicts; do
    if ! is_known_phase1_conflict "$path"; then
      fail "Refusing automatic resolution for unknown conflict: $path"
    fi
  done

  git checkout --ours -- $conflicts
  git add $conflicts
  echo "Resolved known Phase 1 scaffold conflicts by keeping the current branch side."
fi

git diff --check --cached
sh scripts/ci/validate-phase1.sh
sh scripts/test/checkout-app.sh

cat <<EOF
Merge validation passed.

If Git says the merge is still in progress, conclude it with:

  git commit -m "Merge main into $(git branch --show-current)"

Push only when explicitly requested:

  git push $remote $(git branch --show-current)
EOF
