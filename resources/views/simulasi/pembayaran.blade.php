@use('App\Support\Format')

<x-layouts.app title="Simulasi pembayaran">
    <main id="konten" class="halaman flex min-h-screen flex-col justify-center py-12">
        <div class="mx-auto w-full max-w-lg">
            <p class="mb-4 border-2 border-dashed border-cap px-4 py-3 font-bold text-cap">
                Halaman simulasi, khusus lingkungan lokal. Tidak ada uang yang berpindah. Tombol di bawah meniru kabar dari payment gateway.
            </p>

            <x-auth.sheet heading="Simulasi pembayaran" :note="$pembayaran->id_pembayaran">
                <dl class="grid grid-cols-[8rem_1fr] gap-y-2">
                    <dt class="text-pensil-muda">Pesanan</dt><dd class="font-bold">{{ $pembayaran->id_pesanan }}</dd>
                    <dt class="text-pensil-muda">Tutor</dt><dd>{{ $pembayaran->pesanan->tutor->nama }}</dd>
                    <dt class="text-pensil-muda">Cara bayar</dt><dd>{{ $pembayaran->metode->label() }}</dd>
                    <dt class="text-pensil-muda">Jumlah</dt><dd class="angka font-display text-[1.5rem] font-extrabold">{{ Format::rupiah($pembayaran->jumlah) }}</dd>
                </dl>

                <div class="mt-7 flex flex-wrap gap-3">
                    @foreach (['berhasil' => ['Pembayaran berhasil', 'tombol'], 'gagal' => ['Pembayaran gagal', 'tombol-garis tombol-bahaya']] as $status => [$teks, $kelas])
                        <form method="post" action="{{ route('webhooks.payment') }}">
                            <input type="hidden" name="payment_id" value="{{ $pembayaran->id_pembayaran }}">
                            <input type="hidden" name="order_id" value="{{ $pembayaran->id_pesanan }}">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <input type="hidden" name="signature" value="{{ $tandaTangan[$status] }}">
                            <button type="submit" class="{{ $kelas }}">{{ $teks }}</button>
                        </form>
                    @endforeach
                </div>

                <a href="{{ route('pesanan.show', $pembayaran->id_pesanan) }}" class="tautan mt-6 inline-block">Kembali ke pesanan tanpa membayar</a>
            </x-auth.sheet>
        </div>
    </main>
</x-layouts.app>
