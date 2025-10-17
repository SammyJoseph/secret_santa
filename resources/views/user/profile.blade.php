<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
            <div class="grid gap-8 grid-cols-1">
                <div class="flex flex-col">
                    <div class="flex flex-col sm:flex-row items-center">
                        <h2 class="font-semibold text-lg mr-auto">Perfil de Usuario</h2>
                        <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0"></div>
                    </div>
                    <div>
                        <div class="text-center">
                            <p class="text-green-600 font-semibold">¡Registro exitoso! Bienvenido al Amigo Secreto Familiar.</p>
                            <p class="mt-4">Tu perfil ha sido creado correctamente.</p>
                            <a href="{{ route('login') }}" class="mt-6 inline-block bg-[#146B3A] px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg hover:bg-green-500">
                                Ir al Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('css')
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
    </style>
    @endsection
</x-guest-layout>