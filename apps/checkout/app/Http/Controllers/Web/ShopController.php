<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Catalog\CatalogReader;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\ProductCardViewModel;
use App\Http\ViewModels\ProductViewModelFactory;
use App\Http\ViewModels\ShopViewModel;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Renders the tenant-scoped shop landing page.
 */
final class ShopController extends Controller
{
    /**
     * Create the shop page controller.
     */
    public function __construct(
        private readonly CatalogReader $catalog,
        private readonly ProductViewModelFactory $viewModels,
    ) {}

    /**
     * Show products for the resolved tenant.
     */
    public function index(Request $request): View
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);

        $viewModel = new ShopViewModel(
            tenant: $tenant,
            products: $this->catalog
                ->productsForTenant($tenant)
                ->map(fn (ProductRecord $product): ProductCardViewModel => $this->viewModels->card($product))
                ->values()
                ->all(),
        );

        return view('shop.index', ['view' => $viewModel]);
    }
}
