{{-- Chrome renders this outside the page, so it cannot use the embedded font: keep it to Latin text and digits. --}}
<div style="width: 100%; padding: 0 12mm; font-family: Arial, Helvetica, sans-serif; font-size: 8px; color: #64748b; display: flex; justify-content: space-between;">
    <span>{{ $reference }} &middot; v{{ $version }}</span>
    <span>@pageNumber / @totalPages</span>
</div>
