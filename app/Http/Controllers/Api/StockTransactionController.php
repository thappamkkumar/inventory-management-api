<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockTransactionRequest;
use App\Http\Resources\StockTransactionResource;
use App\Models\StockTransaction;
use App\Services\StockTransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Filters\StockTransactionFilter;
use App\Http\Requests\TransferStockRequest;
use App\Models\Inventory;

class StockTransactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StockTransactionService $service
    ) {}

    /**
     * Display stock transactions.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $transactions = StockTransaction::query()
            ->with([
                'inventory.product',
                'inventory.warehouse',
                'user',
            ])
            ->filter(new StockTransactionFilter($request))
            ->paginate($perPage)
            ->withQueryString();

        return StockTransactionResource::collection($transactions);
    }

    /**
     * Store a new stock transaction.
     */
    public function store(StoreStockTransactionRequest $request)
    {
        $transaction = $this->service->create(
            inventory: \App\Models\Inventory::findOrFail(
                $request->integer('inventory_id')
            ),
            type: $request->string('type')->toString(),
            quantity: $request->integer('quantity'),
            reference: $request->input('reference'),
            notes: $request->input('notes'),
            userId: $request->user()->id,
        );

        $transaction->load([
            'inventory.product',
            'inventory.warehouse',
            'user',
        ]);

        return $this->created(
            'Stock transaction created successfully.',
            new StockTransactionResource($transaction)
        );
    }

    /**
     * Display a stock transaction.
     */
    public function show(StockTransaction $stockTransaction)
    {
        $stockTransaction->load([
            'inventory.product',
            'inventory.warehouse',
            'user',
        ]);

        return new StockTransactionResource($stockTransaction);
    }


    /**
     * Transfer stock between inventories.
     */
    public function transfer(TransferStockRequest $request)
    {
        $result = $this->service->transfer(
            fromInventory: Inventory::findOrFail(
                $request->integer('from_inventory_id')
            ),
            toInventory: Inventory::findOrFail(
                $request->integer('to_inventory_id')
            ),
            quantity: $request->integer('quantity'),
            reference: $request->input('reference'),
            notes: $request->input('notes'),
            userId: $request->user()->id,
        );

        $result['transfer_out']->load([
            'inventory.product',
            'inventory.warehouse',
            'user',
        ]);

        $result['transfer_in']->load([
            'inventory.product',
            'inventory.warehouse',
            'user',
        ]);

        return $this->created(
            'Stock transfer completed successfully.',
            [
                'transfer_out' => new StockTransactionResource(
                    $result['transfer_out']
                ),
                'transfer_in' => new StockTransactionResource(
                    $result['transfer_in']
                ),
            ]
        );
    }
}