@extends('layout.base')

@section('content')
    <div class="container">
        <h1>Novo Pedido</h1>
        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="client_id" class="form-label">Cliente</label>
                <select name="client_id" class="form-control" id="client_id" required>
                    <option value="">-- Selecione um Cliente --</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="order_status" class="form-label">Situação do Pedido</label>
                <select name="order_status" class="form-control" id="order_status" required>
                    <option value="Open">Aberto</option>
                    <option value="Paid">Pago</option>
                    <option value="Cancelled">Cancelado</option>
                </select>
            </div>
            <h3>Produtos</h3>
            <div id="products-container">
                <div class="product-item product-box">
                    <div class="row">
                        <div class="col-md-8 col-12 mb-3">
                            <label for="products[0][product_id]" class="form-label">Produto</label>
                            <select name="products[0][product_id]" class="form-control product-select" required>
                                <option value="" data-price="0">-- Selecione um Produto --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->unit_price }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-10 mb-3">
                            <label for="products[0][quantity]" class="form-label">Quantidade</label>
                            <input type="number" name="products[0][quantity]" class="form-control product-quantity" required>
                        </div>
                        <div class="col-md-2 col-2 mb-3 pt-4 d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-danger remove-product"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-center">
                <button type="button" id="add-product" class="btn btn-success"><i class="bi bi-bag-plus"></i></button>
            </div>
            <br><br><hr>
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <label for="subtotal" class="form-label">Subtotal (R$)</label>
                        <input type="number" name="subtotal" class="form-control" id="subtotal" readonly>
                    </div>
                    <div class="col-md-4 col-12">
                        <label for="discount" class="form-label">Desconto (R$)</label>
                        <input type="number" min="0.01" step="0.01" max="999999999999999.99" name="discount" class="form-control" id="discount">
                    </div>
                    <div class="col-md-4 col-12">
                        <label for="total" class="form-label">Total (R$)</label>
                        <input type="number" name="total" class="form-control" id="total" readonly>
                    </div>
                    <div class="col-12 d-grid mt-2">
                        <button type="submit" class="btn btn-primary btn-lg">Confirmar Pedido</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('extraJs')
    <script>
        let productIndex = 1;

        document.getElementById('add-product').addEventListener('click', function () {
            const container = document.getElementById('products-container');
            const productItem = document.createElement('div');
            productItem.classList.add('product-item', 'product-box');
            productItem.innerHTML = `
                <div class="row">
                    <div class="col-md-8 col-12 mb-3">
                        <label for="products[${productIndex}][product_id]" class="form-label">Produto</label>
                        <select name="products[${productIndex}][product_id]" class="form-control product-select" required>
                            <option value="" data-price="0">-- Selecione um Produto --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->unit_price }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-10 mb-3">
                        <label for="products[${productIndex}][quantity]" class="form-label">Quantidade</label>
                        <input type="number" name="products[${productIndex}][quantity]" class="form-control product-quantity" required>
                    </div>
                    <div class="col-md-2 col-2 mb-3 pt-4 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-danger remove-product"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
            container.appendChild(productItem);
            productIndex++;

            updateEventListeners();
        });

        function updateEventListeners() {
            document.querySelectorAll('.remove-product').forEach(button => {
                button.addEventListener('click', function () {
                    this.closest('.product-item').remove();
                    calculateTotals();
                });
            });

            document.querySelectorAll('.product-select, .product-quantity').forEach(input => {
                input.addEventListener('change', calculateTotals);
                input.addEventListener('input', calculateTotals);
            });
        }

        function calculateTotals() {
            let subtotal = 0;
            document.querySelectorAll('.product-item').forEach(item => {
                const productSelect = item.querySelector('.product-select');
                const quantityInput = item.querySelector('.product-quantity');
                const unitPrice = parseFloat(productSelect.selectedOptions[0].getAttribute('data-price'));
                const quantity = parseInt(quantityInput.value);
                if (!isNaN(unitPrice) && !isNaN(quantity)) {
                    subtotal += unitPrice * quantity;
                    document.getElementById('discount').setAttribute('max', subtotal.toFixed(2));
                }
            });

            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const total = subtotal - discount;

            document.getElementById('subtotal').value = subtotal.toFixed(2);
            document.getElementById('total').value = total.toFixed(2);
        }

        function fixDiscount() {
            let subtotal = parseFloat(document.getElementById('subtotal').value);
            if (subtotal < parseFloat(this.value)) {
                this.value = subtotal;
                calculateTotals();
            }
        }

        document.getElementById('discount').addEventListener('input', calculateTotals);
        document.getElementById('discount').addEventListener('change', fixDiscount);
        document.getElementById('discount').addEventListener('input', fixDiscount);
        document.getElementById('discount').addEventListener('keyup', fixDiscount);

        updateEventListeners();
        calculateTotals();
    </script>
@endsection
