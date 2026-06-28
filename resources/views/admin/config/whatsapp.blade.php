@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8" x-data="whatsappConfig()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">WhatsApp</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Conecta tu número de WhatsApp para notificar a tus clientes automáticamente</p>
        </div>
        <a href="{{ route('settings.index') }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            ← Volver
        </a>
    </div>

    {{-- Estado 0: Desconectado / Sin instancia --}}
    <div x-show="estado === 0" x-cloak
         class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-2xl p-8 text-center">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">WhatsApp no conectado</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Conecta tu número de WhatsApp para empezar a enviar notificaciones automáticas a tus clientes.</p>
        <button @click="conectar()" :disabled="loading"
            class="inline-flex items-center gap-2 rounded-md bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors disabled:opacity-50">
            <span x-show="!loading">🔗 Conectar WhatsApp</span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Conectando...
            </span>
        </button>
    </div>

    {{-- Estado 1: Conectando / QR --}}
    <div x-show="estado === 1" x-cloak
         class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-2xl p-8 text-center">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Escanea el código QR</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Abre WhatsApp en tu teléfono > Menú > WhatsApp Web > Escanea este código</p>
        <div class="flex justify-center mb-6">
            <div class="bg-white p-4 rounded-xl shadow-inner border border-gray-200">
                <img x-show="qr" :src="qr" alt="QR Code" class="w-64 h-64">
                <div x-show="!qr" class="w-64 h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                    <svg class="animate-spin h-8 w-8 text-gray-400" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                </div>
            </div>
        </div>
        <p class="text-xs text-gray-400 mb-4">El código se actualiza automáticamente al escanearlo</p>
        <div class="flex justify-center gap-3">
            <button @click="verificarEstado()" :disabled="loading"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-50">
                ✓ Ya escaneé
            </button>
            <button @click="desconectar()" :disabled="loading"
                class="inline-flex items-center rounded-md bg-red-100 dark:bg-red-900/30 px-4 py-2 text-sm font-semibold text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors disabled:opacity-50">
                Cancelar
            </button>
        </div>
    </div>

    {{-- Estado 2: Conectado --}}
    <div x-show="estado === 2" x-cloak>
        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-2xl p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">WhatsApp Conectado ✅</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tu número está listo para enviar notificaciones automáticas</p>
                    </div>
                </div>
                <button @click="desconectar()" :disabled="loading"
                    class="inline-flex items-center rounded-md bg-red-100 dark:bg-red-900/30 px-4 py-2 text-sm font-semibold text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors disabled:opacity-50">
                    Desconectar
                </button>
            </div>
        </div>

        {{-- Notificaciones Pendientes --}}
        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-2xl p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Pendientes</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Mensajes que no pudieron enviarse porque se alcanzó el límite mensual. Puedes reenviarlos manualmente o esperar al próximo mes.</p>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="pendientes.length > 0">
                        <button @click="descartarTodas()"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-500">Descartar todas</button>
                    </template>
                    <button @click="cargarPendientes()"
                        class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        ↻ Recargar
                    </button>
                </div>
            </div>
            <div x-show="pendientes.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                No hay notificaciones pendientes.
            </div>
            <template x-for="p in pendientes" :key="p.id">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl mb-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="`${p.cliente} — ${p.evento}`"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="`${p.destinatarios} destinatario(s) · ${p.created_at}`"></p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="reenviar(p.id)"
                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Reenviar</button>
                        <button @click="descartar(p.id)"
                            class="text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-500">Descartar</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Consumo Mensual --}}
        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-2xl p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Consumo Mensual</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('F Y') }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Mensajes de WhatsApp enviados este mes.</p>
            <div class="flex items-center gap-4">
                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-5 overflow-hidden">
                    <div class="bg-green-500 h-full rounded-full transition-all duration-500"
                         :style="`width: ${infoWhatsapp.limite > 0 ? Math.min(infoWhatsapp.uso / infoWhatsapp.limite * 100, 100) : 0}%`"></div>
                </div>
                <span class="text-sm font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap min-w-[80px] text-right"
                      x-text="`${infoWhatsapp.uso} / ${infoWhatsapp.limite > 0 ? infoWhatsapp.limite : '∞'}`"></span>
            </div>
        </div>
    </div>
</div>

<script>
function whatsappConfig() {
    return {
        estado: {{ $estado }},
        qr: null,
        loading: false,
        pendientes: [],
        infoWhatsapp: { limite: -1, uso: 0 },
        pollingTimer: null,

        init() {
            this.cargarPendientes();
            if (this.estado === 1) {
                this.iniciarPolling();
                if (!this.qr) {
                    this.regenerarQr();
                }
            }
        },

        async regenerarQr() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('whatsapp.conectar') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
                const data = await res.json();
                if (data.qr) {
                    this.qr = data.qr;
                    this.estado = 1;
                    this.iniciarPolling();
                } else if (data.paired) {
                    this.estado = 2;
                    this.detenerPolling();
                } else {
                    this.qr = null;
                    console.warn('[WhatsApp] No se pudo regenerar el QR:', data.message ?? 'sin respuesta');
                }
            } catch (e) {
                console.error('[WhatsApp] Error regenerando QR:', e);
                this.qr = null;
            } finally {
                this.loading = false;
            }
        },

        async conectar() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('whatsapp.conectar') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
                const data = await res.json();
                if (data.qr) {
                    this.qr = data.qr;
                    this.estado = 1;
                    this.iniciarPolling();
                } else if (data.paired) {
                    this.estado = 2;
                } else {
                    alert(data.message || 'Error al conectar');
                }
            } catch (e) {
                alert('Error de conexión con el servidor');
            } finally {
                this.loading = false;
            }
        },

        async verificarEstado() {
            try {
                const res = await fetch('{{ route('whatsapp.estado') }}');
                const data = await res.json();
                if (data.connected) {
                    this.estado = 2;
                    this.qr = null;
                    this.detenerPolling();
                    this.cargarPendientes();
                } else if (this.estado === 1 && !this.qr) {
                    this.regenerarQr();
                }
            } catch (e) {}
        },

        async desconectar() {
            if (!confirm('¿Desconectar WhatsApp? Si lo haces, deberás volver a escanear un código QR para reconectar.')) return;
            this.loading = true;
            try {
                await fetch('{{ route('whatsapp.desconectar') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
                this.estado = 1;
                this.qr = null;
                this.detenerPolling();
                this.iniciarPolling();
                this.regenerarQr();
            } finally {
                this.loading = false;
            }
        },

        async cargarPendientes() {
            try {
                const res = await fetch('{{ route('whatsapp.pendientes') }}');
                const data = await res.json();
                if (data.success) {
                    this.pendientes = data.pendientes_whatsapp ?? [];
                    this.infoWhatsapp = data.info_whatsapp ?? { limite: -1, uso: 0 };
                }
            } catch (e) {}
        },

        async reenviar(id) {
            try {
                const res = await fetch('{{ route('whatsapp.reenviar-pendiente') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({ id }),
                });
                const data = await res.json();
                if (data.success) {
                    this.cargarPendientes();
                } else {
                    alert(data.message || 'Error al reenviar');
                }
            } catch (e) { alert('Error al reenviar'); }
        },

        async descartar(id) {
            try {
                await fetch('{{ route('whatsapp.descartar-pendiente') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({ id }),
                });
                this.cargarPendientes();
            } catch (e) {}
        },

        async descartarTodas() {
            if (!confirm('¿Descartar todas las notificaciones pendientes?')) return;
            try {
                await fetch('{{ route('whatsapp.descartar-todas-pendientes') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                });
                this.cargarPendientes();
            } catch (e) {}
        },

        iniciarPolling() {
            this.detenerPolling();
            this.pollingTimer = setInterval(() => this.verificarEstado(), 5000);
        },

        detenerPolling() {
            if (this.pollingTimer) {
                clearInterval(this.pollingTimer);
                this.pollingTimer = null;
            }
        },
    };
}
</script>
@endsection
