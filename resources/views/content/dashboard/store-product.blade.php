@extends('layouts/contentNavbarLayout')

@section('title', 'Store - Product')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">STORE · Product</h4>
                <p class="mb-0">Manajemen product untuk toko digital: Video Asset, Plugin, LUT, Music Asset, Photo Asset, template, dan produk lainnya.</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (!($is_admin ?? false))
                    <div class="alert alert-info mb-4">Mode non-admin: Anda hanya bisa menambah product baru. Edit, hapus, atau penyesuaian product yang sudah ada hanya untuk Admin.</div>
                @endif

                <form method="POST" action="{{ route('dashboard-store-product.update') }}" class="row g-4" id="storeProductForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="action" value="save">

                    <div class="col-md-4">
                        <label class="form-label">Default Currency</label>
                        <select class="form-select" name="currency">
                            <option value="IDR" @selected(old('currency', $form['currency']) === 'IDR')>IDR</option>
                            <option value="USD" @selected(old('currency', $form['currency']) === 'USD')>USD</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax Percent</label>
                        <input class="form-control" type="number" step="0.01" min="0" max="100" name="tax_percent" value="{{ old('tax_percent', $form['tax_percent']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Checkout Mode</label>
                        <select class="form-select" name="checkout_mode">
                            <option value="manual" @selected(old('checkout_mode', $form['checkout_mode']) === 'manual')>Manual Transfer</option>
                            <option value="midtrans" @selected(old('checkout_mode', $form['checkout_mode']) === 'midtrans')>Midtrans</option>
                            <option value="xendit" @selected(old('checkout_mode', $form['checkout_mode']) === 'xendit')>Xendit</option>
                            <option value="stripe" @selected(old('checkout_mode', $form['checkout_mode']) === 'stripe')>Stripe</option>
                            <option value="doku" @selected(old('checkout_mode', $form['checkout_mode']) === 'doku')>DOKU</option>
                        </select>
                    </div>
                    <div class="col-12 mt-1">
                        <h6 class="mb-0">DOKU Gateway</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">DOKU Checkout URL</label>
                        <input class="form-control" type="url" name="doku_checkout_url" value="{{ old('doku_checkout_url', $form['doku_checkout_url']) }}" placeholder="https://pay.doku.com/checkout/link">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DOKU Merchant ID</label>
                        <input class="form-control" name="doku_merchant_id" value="{{ old('doku_merchant_id', $form['doku_merchant_id']) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DOKU Client ID</label>
                        <input class="form-control" name="doku_client_id" value="{{ old('doku_client_id', $form['doku_client_id']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">DOKU Shared Key</label>
                        <input class="form-control" type="password" name="doku_shared_key" value="{{ old('doku_shared_key', $form['doku_shared_key']) }}" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">DOKU Notify Token</label>
                        <input class="form-control" type="password" name="doku_notify_token" value="{{ old('doku_notify_token', $form['doku_notify_token']) }}" autocomplete="off">
                    </div>
                    <div class="col-12">
                        <small class="text-muted">Webhook production memverifikasi Signature DOKU resmi (Client-Id, Request-Id, Request-Timestamp, Request-Target, Digest, Signature HMACSHA256).</small>
                    </div>

                    <div class="col-12 d-flex justify-content-between align-items-center mt-2">
                        <h6 class="mb-0">Product Items</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddProduct">Tambah Product</button>
                    </div>

                    @php $initialProducts = old('products', $form['products_form']); @endphp
                    <div class="col-12">
                        <div id="productRows" class="d-grid gap-3">
                            @foreach ($initialProducts as $index => $product)
                                <div class="border rounded p-3 product-row" data-index="{{ $index }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <strong>Product #{{ $loop->iteration }}</strong>
                                        @if ($is_admin ?? false)
                                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-product">Hapus</button>
                                        @endif
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Nama Product</label>
                                            <input class="form-control" name="products[{{ $index }}][name]" value="{{ $product['name'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Jenis</label>
                                            <input class="form-control" list="storeProductTypeSuggestions" name="products[{{ $index }}][type]" value="{{ $product['type'] ?? '' }}" placeholder="Contoh: Resolume Arena Plugin" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Price</label>
                                            <input class="form-control" type="number" min="0" step="0.01" name="products[{{ $index }}][price]" value="{{ $product['price'] ?? 0 }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">SKU</label>
                                            <input class="form-control" name="products[{{ $index }}][sku]" value="{{ $product['sku'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Stock <small class="text-muted">(0 = Tidak terbatas)</small></label>
                                            <input class="form-control" type="number" min="0" name="products[{{ $index }}][stock]" value="{{ $product['stock'] ?? 0 }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="products[{{ $index }}][status]">
                                                <option value="draft" @selected(($product['status'] ?? '') === 'draft')>Draft</option>
                                                <option value="published" @selected(($product['status'] ?? '') === 'published')>Published</option>
                                                <option value="archived" @selected(($product['status'] ?? '') === 'archived')>Archived</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Category</label>
                                            <input class="form-control" name="products[{{ $index }}][category]" value="{{ $product['category'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">License Type</label>
                                            <input class="form-control" name="products[{{ $index }}][license_type]" value="{{ $product['license_type'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Thumbnail Upload (Wajib, URL Alternatif)</label>
                                            <input class="form-control js-image-input" data-preview-target=".js-thumb-preview" type="file" name="products[{{ $index }}][thumbnail_file]" accept=".jpg,.jpeg,.png,.webp">
                                            <input type="hidden" name="products[{{ $index }}][thumbnail_existing]" value="{{ $product['thumbnail'] ?? '' }}">
                                            @if (($product['thumbnail'] ?? '') !== '')
                                                <small class="text-muted">Tersimpan: {{ $product['thumbnail'] }}</small>
                                            @endif
                                            <div class="mt-2">
                                                <img src="{{ $product['thumbnail'] ?? '' }}" class="img-thumbnail js-thumb-preview @if(($product['thumbnail'] ?? '') === '') d-none @endif" style="max-height: 120px;" alt="Thumbnail Preview">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Thumbnail URL (Alternatif)</label>
                                            <input class="form-control" name="products[{{ $index }}][thumbnail]" value="{{ $product['thumbnail'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Photo Upload (Wajib, URL Alternatif)</label>
                                            <input class="form-control js-image-input" data-preview-target=".js-photo-preview" type="file" name="products[{{ $index }}][photo_file]" accept=".jpg,.jpeg,.png,.webp">
                                            <input type="hidden" name="products[{{ $index }}][photo_existing]" value="{{ $product['photo'] ?? '' }}">
                                            @if (($product['photo'] ?? '') !== '')
                                                <small class="text-muted">Tersimpan: {{ $product['photo'] }}</small>
                                            @endif
                                            <div class="mt-2">
                                                <img src="{{ $product['photo'] ?? '' }}" class="img-thumbnail js-photo-preview @if(($product['photo'] ?? '') === '') d-none @endif" style="max-height: 120px;" alt="Photo Preview">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Photo URL (Alternatif)</label>
                                            <input class="form-control" name="products[{{ $index }}][photo]" value="{{ $product['photo'] ?? '' }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Product File (Wajib Upload)</label>
                                            <input class="form-control" type="file" name="products[{{ $index }}][asset_file]">
                                            <input type="hidden" name="products[{{ $index }}][asset_path_existing]" value="{{ $product['asset_path'] ?? '' }}">
                                            @if (($product['asset_path'] ?? '') !== '')
                                                <small class="text-muted">Tersimpan: {{ basename($product['asset_path']) }}</small>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Download URL (Opsional)</label>
                                            <input class="form-control" name="products[{{ $index }}][download_url]" value="{{ $product['download_url'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Deskripsi Product</label>
                                            <textarea class="form-control" rows="3" name="products[{{ $index }}][description]">{{ $product['description'] ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Fungsi Product</label>
                                            <textarea class="form-control" rows="3" name="products[{{ $index }}][function]">{{ $product['function'] ?? '' }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Tags (comma separated)</label>
                                            <input class="form-control" name="products[{{ $index }}][tags]" value="{{ $product['tags'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Products JSON (read-only preview)</label>
                        <textarea class="form-control font-monospace" rows="8" readonly>{{ old('products_json', $form['products_json']) }}</textarea>
                    </div>
                    <datalist id="storeProductTypeSuggestions">
                        <option value="Video Asset"></option>
                        <option value="Plugin"></option>
                        <option value="Resolume Arena Plugin"></option>
                        <option value="LUT"></option>
                        <option value="Music Asset"></option>
                        <option value="Photo Asset"></option>
                        <option value="Template"></option>
                        <option value="Other"></option>
                    </datalist>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Product Store</button>
                    </div>
                </form>

                @if (count($revisions) > 0)
                    <div class="border rounded p-3 mt-4">
                        <h6 class="mb-3">Revision History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Summary</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revisions as $index => $revision)
                                        <tr>
                                            <td>{{ $revision['saved_at'] ?? '-' }}</td>
                                            <td>{{ $revision['summary'] ?? 'Autosave' }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('dashboard-store-product.update') }}">
                                                    @csrf
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="restore_revision_index" value="{{ $index }}">
                                                    <input type="hidden" name="currency" value="{{ old('currency', $form['currency']) }}">
                                                    <input type="hidden" name="tax_percent" value="{{ old('tax_percent', $form['tax_percent']) }}">
                                                    <input type="hidden" name="checkout_mode" value="{{ old('checkout_mode', $form['checkout_mode']) }}">
                                                    <input type="hidden" name="doku_checkout_url" value="{{ old('doku_checkout_url', $form['doku_checkout_url']) }}">
                                                    <input type="hidden" name="doku_merchant_id" value="{{ old('doku_merchant_id', $form['doku_merchant_id']) }}">
                                                    <input type="hidden" name="doku_client_id" value="{{ old('doku_client_id', $form['doku_client_id']) }}">
                                                    <input type="hidden" name="doku_shared_key" value="{{ old('doku_shared_key', $form['doku_shared_key']) }}">
                                                    <input type="hidden" name="doku_notify_token" value="{{ old('doku_notify_token', $form['doku_notify_token']) }}">
                                                    @foreach ($form['products_form'] as $pIndex => $productItem)
                                                        <input type="hidden" name="products[{{ $pIndex }}][name]" value="{{ $productItem['name'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][type]" value="{{ $productItem['type'] ?? 'other' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][sku]" value="{{ $productItem['sku'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][price]" value="{{ $productItem['price'] ?? 0 }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][stock]" value="{{ $productItem['stock'] ?? 0 }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][thumbnail]" value="{{ $productItem['thumbnail'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][thumbnail_existing]" value="{{ $productItem['thumbnail'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][photo]" value="{{ $productItem['photo'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][photo_existing]" value="{{ $productItem['photo'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][download_url]" value="{{ $productItem['download_url'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][asset_path_existing]" value="{{ $productItem['asset_path'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][description]" value="{{ $productItem['description'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][function]" value="{{ $productItem['function'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][category]" value="{{ $productItem['category'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][tags]" value="{{ $productItem['tags'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][license_type]" value="{{ $productItem['license_type'] ?? '' }}">
                                                        <input type="hidden" name="products[{{ $pIndex }}][status]" value="{{ $productItem['status'] ?? 'draft' }}">
                                                    @endforeach
                                                    @if ($is_admin ?? false)
                                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Restore</button>
                                                    @else
                                                        <span class="text-muted small">Admin only</span>
                                                    @endif
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    (() => {
        const isAdmin = "{{ ($is_admin ?? false) ? '1' : '0' }}" === '1';
        const productRows = document.getElementById('productRows');
        const addButton = document.getElementById('btnAddProduct');
        if (!(productRows instanceof HTMLElement) || !(addButton instanceof HTMLButtonElement)) {
            return;
        }

        let counter = productRows.querySelectorAll('.product-row').length;
        const bindImagePreview = (scope) => {
            const inputs = scope.querySelectorAll('.js-image-input');
            inputs.forEach((input) => {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }
                input.addEventListener('change', () => {
                    const targetSelector = input.dataset.previewTarget;
                    if (typeof targetSelector !== 'string' || targetSelector === '') {
                        return;
                    }
                    const row = input.closest('.product-row');
                    if (!(row instanceof HTMLElement)) {
                        return;
                    }
                    const preview = row.querySelector(targetSelector);
                    if (!(preview instanceof HTMLImageElement)) {
                        return;
                    }
                    const file = input.files && input.files.length > 0 ? input.files[0] : null;
                    if (!file) {
                        return;
                    }
                    const url = URL.createObjectURL(file);
                    preview.src = url;
                    preview.classList.remove('d-none');
                });
            });
        };

        const template = (index) => `
            <div class="border rounded p-3 product-row" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <strong>Product Baru</strong>
                    ${(isAdmin ? '<button type="button" class="btn btn-sm btn-outline-danger js-remove-product">Hapus</button>' : '')}
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nama Product</label>
                        <input class="form-control" name="products[${index}][name]" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis</label>
                        <input class="form-control" list="storeProductTypeSuggestions" name="products[${index}][type]" value="Video Asset" placeholder="Contoh: Resolume Arena Plugin" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Price</label>
                        <input class="form-control" type="number" min="0" step="0.01" name="products[${index}][price]" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SKU</label>
                        <input class="form-control" name="products[${index}][sku]">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock <small class="text-muted">(0 = Tidak terbatas)</small></label>
                        <input class="form-control" type="number" min="0" name="products[${index}][stock]" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="products[${index}][status]">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input class="form-control" name="products[${index}][category]">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">License Type</label>
                        <input class="form-control" name="products[${index}][license_type]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail Upload (Wajib, URL Alternatif)</label>
                        <input class="form-control js-image-input" data-preview-target=".js-thumb-preview" type="file" name="products[${index}][thumbnail_file]" accept=".jpg,.jpeg,.png,.webp">
                        <input type="hidden" name="products[${index}][thumbnail_existing]" value="">
                        <div class="mt-2">
                            <img src="" class="img-thumbnail js-thumb-preview d-none" style="max-height: 120px;" alt="Thumbnail Preview">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail URL (Alternatif)</label>
                        <input class="form-control" name="products[${index}][thumbnail]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo Upload (Wajib, URL Alternatif)</label>
                        <input class="form-control js-image-input" data-preview-target=".js-photo-preview" type="file" name="products[${index}][photo_file]" accept=".jpg,.jpeg,.png,.webp">
                        <input type="hidden" name="products[${index}][photo_existing]" value="">
                        <div class="mt-2">
                            <img src="" class="img-thumbnail js-photo-preview d-none" style="max-height: 120px;" alt="Photo Preview">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo URL (Alternatif)</label>
                        <input class="form-control" name="products[${index}][photo]">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Download URL</label>
                        <input class="form-control" type="file" name="products[${index}][asset_file]">
                        <input type="hidden" name="products[${index}][asset_path_existing]" value="">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Download URL (Opsional)</label>
                        <input class="form-control" name="products[${index}][download_url]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Deskripsi Product</label>
                        <textarea class="form-control" rows="3" name="products[${index}][description]"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fungsi Product</label>
                        <textarea class="form-control" rows="3" name="products[${index}][function]"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tags (comma separated)</label>
                        <input class="form-control" name="products[${index}][tags]">
                    </div>
                </div>
            </div>
        `;

        addButton.addEventListener('click', () => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = template(counter);
            const row = wrapper.firstElementChild;
            if (row instanceof HTMLElement) {
                productRows.appendChild(row);
                bindImagePreview(row);
                counter += 1;
            }
        });

        productRows.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }
            if (!target.classList.contains('js-remove-product')) {
                return;
            }
            const row = target.closest('.product-row');
            if (!(row instanceof HTMLElement)) {
                return;
            }
            row.remove();
        });
        bindImagePreview(productRows);
    })();
</script>
@endsection
