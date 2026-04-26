<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Catalog\CatalogReader;
use App\Domain\Tenant\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\ProductViewModelFactory;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Renders tenant-scoped product detail pages.
 */
final class ProductController extends Controller
{
    /**
     * Create the product page controller.
     */
    public function __construct(
        private readonly CatalogReader $catalog,
        private readonly ProductViewModelFactory $viewModels,
    ) {}

    /**
     * Show a product by slug for the resolved tenant.
     */
    public function show(Request $request, string $slug): View
    {
        /** @var TenantContext $tenant */
        $tenant = $request->attributes->get(TenantContext::class);
        $product = $this->catalog->productBySlug($tenant, $slug);

        abort_if($product === null, 404);

        return view('product.show', [
            'view' => $this->viewModels->detail($tenant, $product),
        ]);
    }
}
