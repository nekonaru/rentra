<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ 'Terima kasih sudah mendaftar! Sebelum mulai, verifikasi alamat email kamu dengan mengklik tautan yang baru kami kirim. Kalau emailnya belum masuk, kami kirimkan lagi dengan senang hati.' }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ 'Tautan verifikasi baru sudah dikirim ke email yang kamu pakai saat mendaftar.' }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ 'Kirim Ulang Email Verifikasi' }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ 'Keluar' }}
            </button>
        </form>
    </div>
</x-guest-layout>



