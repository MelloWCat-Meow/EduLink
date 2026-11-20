@use('App\Enums\StatusVerifikasiTutor')
<p>Halo {{ $tutor->nama }},</p>

@if ($keputusan === StatusVerifikasiTutor::Terverifikasi)
    <p>Pendaftaran Anda sudah diperiksa dan <strong>disetujui</strong>. Profil Anda sekarang tampil di pencarian EduLink:</p>
    <p><a href="{{ route('tutor.show', $tutor) }}">{{ route('tutor.show', $tutor) }}</a></p>
@elseif ($keputusan === StatusVerifikasiTutor::PerluPerbaikan)
    <p>Pendaftaran Anda sudah diperiksa dan <strong>perlu diperbaiki</strong> sebelum bisa disetujui.</p>
    @if ($catatan)<p>{{ $catatan }}</p>@endif
    @if ($labelBagian)
        <ul>
            @foreach ($labelBagian as $label => $isi)
                <li><strong>{{ $label }}</strong>@if ($isi): {{ $isi }}@endif</li>
            @endforeach
        </ul>
    @endif
    <p>Masuk ke <a href="{{ route('login') }}">{{ route('login') }}</a>, perbaiki bagian yang ditandai, lalu kirim lagi.</p>
@else
    <p>Pendaftaran Anda sudah diperiksa dan <strong>belum bisa kami terima</strong>.</p>
    <p>Alasan: {{ $catatan }}</p>
@endif

<p>Tim Akademik EduLink</p>
