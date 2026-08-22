@extends('layouts.app')

@section('title', 'Crear/Editar ' . ucfirst($type) . ' - CorRol')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    
    @include('partials.creation-nav')

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 shadow-lg">
                <h3 class="font-bold text-sm text-indigo-400 mb-3 flex justify-between items-center">
                    <span>Mis {{ ucfirst($type) }}s</span>
                    <a href="{{ route($type === 'sheet' ? 'sheets.create' : ($type === 'diary' ? 'diaries.create' : 'campaigns.create')) }}" class="text-xs text-emerald-400 hover:underline">+ Nuevo</a>
                </h3>

                <div class="space-y-2 max-h-[500px] overflow-y-auto">
                    @forelse($userResources as $res)
                        <a href="?edit_id={{ $res->id }}" 
                           class="block p-3 rounded-lg border text-xs transition-all {{ ($editingResource && $editingResource->id === $res->id) ? 'bg-indigo-950 border-indigo-500 text-white font-bold' : 'bg-slate-900 border-slate-700/60 text-slate-300 hover:border-indigo-500' }}">
                            <div class="truncate">{{ $res->title }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ $res->created_at->format('d/m/Y') }}</div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 italic">No tienes ningún registro aún.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl space-y-6">
            <h1 class="text-2xl font-bold text-indigo-400">
                @if(!empty($isClone))
                    📋 Clonar {{ ucfirst($type) }} (Nueva Copia)
                @elseif($editingResource)
                    ✏️ Editar {{ ucfirst($type) }}
                @else
                    📝 Crear Nuevo/a {{ ucfirst($type) }}
                @endif
            </h1>

            <form id="sheet-form" action="{{ route('sheets.store') }}" method="POST" class="space-y-6">
                @csrf
                {{-- Solo enviamos resource_id si estamos editando de verdad, no al clonar --}}
                @if($editingResource && empty($isClone))
                    <input type="hidden" name="resource_id" value="{{ $editingResource->id }}">
                @endif
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="content_json" id="content_json">

                @php
                    $defaultTitle = $editingResource ? (!empty($isClone) ? '[Copia] ' . $editingResource->title : $editingResource->title) : '';
                @endphp
                <div>
                    <label class="block text-sm font-medium mb-2">Título *</label>
                    <input type="text" name="title" value="{{ old('title', $defaultTitle) }}" required 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Juego de Rol</label>
                        <input type="text" name="game" list="games-list" placeholder="Ej: RuneQuest" value="{{ old('game', $editingResource->game ?? '') }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                        <datalist id="games-list">
                            @foreach ($games as $g)
                                <option value="{{ $g }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Campaña</label>
                        <input type="text" name="campaign" list="campaigns-list" placeholder="Ej: Juego de Dioses" value="{{ old('campaign', $editingResource->campaign ?? '') }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                        <datalist id="campaigns-list">
                            @foreach ($campaigns as $c)
                                <option value="{{ $c }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Privacidad</label>
                    <select name="privacy" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none">
                        <option value="public" {{ old('privacy', $editingResource->privacy ?? 'public') === 'public' ? 'selected' : '' }}>Público</option>
                        <option value="private" {{ old('privacy', $editingResource->privacy ?? 'public') === 'private' ? 'selected' : '' }}>Privado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Contenido de la Ficha / Diario *</label>
                    <div id="editorjs" data-content='{!! json_encode($editingResource && $editingResource->resourceable ? ($editingResource->resourceable->content ?? new \stdClass()) : new \stdClass()) !!}' class="bg-slate-900 border border-slate-700 rounded-lg p-4 min-h-[350px] text-slate-100 cursor-text"></div>
                </div>

                <button type="button" onclick="submitSheetForm()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg">
                    Guardar {{ ucfirst($type) }}
                </button>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editorEl = document.getElementById('editorjs');
            const rawContent = JSON.parse(editorEl.getAttribute('data-content') || '{}');

            const tools = {};
            if (typeof Header !== 'undefined') {
                tools.header = {
                    class: Header,
                    inlineToolbar: true
                };
            }
            if (typeof Table !== 'undefined') {
                tools.table = {
                    class: Table,
                    inlineToolbar: true
                };
            }

            window.editor = new EditorJS({
                holder: 'editorjs',
                data: rawContent,
                tools: tools,
                placeholder: 'Haz clic aquí para empezar a escribir tu Ficha o Diario...'
            });
        });

        function submitSheetForm() {
            if (window.editor) {
                window.editor.save().then(function(outputData) {
                    document.getElementById('content_json').value = JSON.stringify(outputData);
                    document.getElementById('sheet-form').submit();
                }).catch(function(error) {
                    console.error('Error guardando Editor.js:', error);
                    alert('Ocurrió un error al procesar el contenido del editor.');
                });
            } else {
                document.getElementById('sheet-form').submit();
            }
        }
    </script>
</div>
@endsection