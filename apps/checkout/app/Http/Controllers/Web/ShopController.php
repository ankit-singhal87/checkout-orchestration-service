<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Catalog\CatalogReader;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\ProductViewModelFactory;
use App\Http\ViewModels\ShopViewModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ShopController extends Controller
{
    public function __construct(
        private readonly CatalogReader $catalog,
        private readonly ProductViewModelFactory $viewModels,
    ) {}

    public function index(Request $request): View
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);

        $viewModel = new ShopViewModel(
            tenant: $tenant,
            products: $this->catalog
                ->productsForTenant($tenant)
                ->map(fn ($product) => $this->viewModels->card($product))
                ->values()
                ->all(),
        );

        return view('shop.index', ['view' => $viewModel]);
    }
}
