<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-display font-bold text-arang dark:text-paper">Peta Meja</h2>
            <p class="text-sm text-muted-dark dark:text-muted-light">Kelola dan pantau status meja secara visual</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="toggleEditMode" class="px-4 py-2 rounded-lg transition-colors font-medium text-sm flex items-center gap-2
                {{ $editMode ? 'bg-cabai hover:bg-cabai/90 text-white' : 'bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>{{ $editMode ? 'Selesai Edit' : 'Edit Tata Letak' }}</span>
            </button>
            <a href="{{ route('kasir.dashboard') }}" class="px-4 py-2 bg-gas hover:bg-gas/90 text-white rounded-lg transition-colors font-medium text-sm">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- CANVAS --}}
    <div class="bg-surface/30 dark:bg-ink/30 rounded-2xl border border-border-light dark:border-border-dark overflow-hidden relative">
        {{-- Canvas Grid --}}
        <div class="absolute inset-0 opacity-10" style="background-image: url(\"data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 40H40M40 0V40' stroke='currentColor' stroke-width='0.5'/%3E%3C/svg%3E\")"></div>

        <div class="relative min-h-[600px] touch-none" x-data="tableMapCanvas()" x-init="initCanvas()">
            <canvas id="table-canvas" class="w-full h-full cursor-default"
                :class="{ 'cursor-grabbing': isDragging }"
                @mousedown="onMouseDown($event)"
                @mousemove="onMouseMove($event)"
                @mouseup="onMouseUp($event)"
                @mouseleave="onMouseUp($event)"
                @touchstart.prevent="onTouchStart($event)"
                @touchmove.prevent="onTouchMove($event)"
                @touchend="onTouchEnd($event)">
            </canvas>

            {{-- Meja Cards (overlay) --}}
            <div class="absolute inset-0 pointer-events-none">
                @foreach($mejaList as $meja)
                    <div wire:key="table-map-{{ $meja->id }}"
                        class="absolute pointer-events-auto"
                        :style="'left: ' + {{ $meja->pos_x ?? 50 }} + 'px; top: ' + {{ $meja->pos_y ?? 50 }} + 'px;'"
                        x-data="{ mejaId: {{ $meja->id }}, isDragging: false }"
                        @mousedown.prevent="startDrag(mejaId, $event)"
                        @touchstart.prevent="startDrag(mejaId, $event)"
                        @click="onMejaClick(mejaId, $event)">
                    <div class="relative w-28 h-28 sm:w-32 sm:h-32"
                        :class="{
                            'ring-2 ring-cabai': mejaStatus(mejaId) === 'butuh-bayar',
                            'ring-2 ring-gas': mejaStatus(mejaId) === 'terisi',
                            'ring-2 ring-daun': mejaStatus(mejaId) === 'kosong',
                            'ring-2 ring-gray-500': mejaStatus(mejaId) === 'nonaktif',
                        }">
                        {{-- Meja Shape --}}
                        <div class="w-full h-full rounded-2xl bg-paper-card dark:bg-surface border-2 border-border-light dark:border-border-dark flex flex-col items-center justify-center shadow-lg transition-all duration-300
                            {{ $editMode ? 'opacity-75 cursor-move' : 'hover:shadow-xl hover:-translate-y-1' }}">
                            <div class="flex-1 flex flex-col items-center justify-center p-3">
                                <span class="text-2xl sm:text-3xl font-display font-bold text-arang dark:text-kertas">{{ $meja->nomor }}</span>
                                <span class="text-xs text-muted-dark dark:text-muted-light font-mono">{{ $meja->pos_x ?? 50 }}, {{ $meja->pos_y ?? 50 }}</span>
                            </div>

                            {{-- Status Badge --}}
                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full
                                    {{ mejaStatusClass(mejaId) }}">
                                    {{ mejaStatusLabel(mejaId) }}
                                </span>
                            </div>

                            {{-- Order Indicator --}}
                            @if(mejaHasActiveOrder(mejaId))
                                <div class="absolute top-1 right-1 w-5 h-5 bg-accent rounded-full flex items-center justify-center animate-pulse">
                                    <svg class="w-3 h-3 text-ink" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
            </div>
        </div>
    </div>

    {{-- LEGEND --}}
    <div class="flex flex-wrap items-center gap-4 bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-4">
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-daun/20 border border-daun/30"></span>
            <span class="text-xs text-arang dark:text-kertas">Kosong</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-gas/20 border border-gas/30"></span>
            <span class="text-xs text-arang dark:text-kertas">Terisi</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-cabai/20 border border-cabai/30"></span>
            <span class="text-xs text-arang dark:text-kertas">Butuh Bayar</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-gray-500/20 border border-gray-500/30"></span>
            <span class="text-xs text-arang dark:text-kertas">Nonaktif</span>
        </div>
    </div>

    {{-- EDIT MODE HELP --}}
    @if($editMode)
        <div class="fixed bottom-4 right-4 bg-cabai/90 text-white px-4 py-2 rounded-xl shadow-lg text-sm animate-bounce">
            Seret meja ke posisi baru, lalu lepas untuk menyimpan
        </div>
    @endif
</div>

@push('scripts')
<script>
    function mejaStatus(mejaId) {
        const meja = @json($mejaList->keyBy('id'));
        const m = meja[mejaId];
        if (!m) return 'kosong';
        
        const hasOrder = m.pesanan && m.pesanan.length > 0;
        const activeOrder = hasOrder ? m.pesanan[0] : null;
        
        if (!m.status || m.status !== 'Aktif') return 'nonaktif';
        if (!activeOrder) return 'kosong';
        if (activeOrder.status === 'Selesai') return 'butuh-bayar';
        return 'terisi';
    }

    function mejaStatusClass(mejaId) {
        const status = mejaStatus(mejaId);
        switch(status) {
            case 'kosong': return 'bg-daun/20 text-daun border border-daun/30';
            case 'terisi': return 'bg-gas/20 text-gas border border-gas/30';
            case 'butuh-bayar': return 'bg-cabai/20 text-cabai border border-cabai/30';
            case 'nonaktif': return 'bg-gray-500/20 text-gray-500 border border-gray-500/30';
            default: return 'bg-gray-500/20 text-gray-500 border border-gray-500/30';
        }
    }

    function mejaStatusLabel(mejaId) {
        const status = mejaStatus(mejaId);
        switch(status) {
            case 'kosong': return 'Kosong';
            case 'terisi': return 'Terisi';
            case 'butuh-bayar': return 'Butuh Bayar';
            case 'nonaktif': return 'Nonaktif';
            default: return 'Kosong';
        }
    }

    function mejaHasActiveOrder(mejaId) {
        const meja = @json($mejaList->keyBy('id'));
        const m = meja[mejaId];
        if (!m || !m.pesanan || m.pesanan.length === 0) return false;
        const activeOrder = m.pesanan[0];
        return ['Menunggu', 'Diterima', 'Diproses', 'Selesai'].includes(activeOrder.status);
    }

    function tableMapCanvas() {
        return {
            canvas: null,
            ctx: null,
            mejaData: @json($mejaList->map(fn(m) => ['id' => m.id, 'nomor' => m.nomor, 'x' => m.pos_x ?? 50, 'y' => m.pos_y ?? 50, 'status' => mejaStatus(m.id), 'hasOrder' => mejaHasActiveOrder(m.id)])->toArray()),
            isDragging: false,
            draggedMejaId: null,
            dragOffsetX: 0,
            dragOffsetY: 0,
            editMode: @json($editMode),

            initCanvas() {
                this.canvas = document.getElementById('table-canvas');
                this.ctx = this.canvas.getContext('2d');
                this.resizeCanvas();
                window.addEventListener('resize', () => this.resizeCanvas());
                this.draw();
            },

            resizeCanvas() {
                const parent = this.canvas.parentElement;
                this.canvas.width = parent.clientWidth;
                this.canvas.height = parent.clientHeight;
                this.draw();
            },

            draw() {
                if (!this.ctx) return;
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                
                // Draw grid
                this.ctx.strokeStyle = '#e5e7eb';
                this.ctx.lineWidth = 0.5;
                const gridSize = 40;
                for (let x = 0; x <= this.canvas.width; x += gridSize) {
                    this.ctx.beginPath();
                    this.ctx.moveTo(x, 0);
                    this.ctx.lineTo(x, this.canvas.height);
                    this.ctx.stroke();
                }
                for (let y = 0; y <= this.canvas.height; y += gridSize) {
                    this.ctx.beginPath();
                    this.ctx.moveTo(0, y);
                    this.ctx.lineTo(this.canvas.width, y);
                    this.ctx.stroke();
                }

                // Draw meja positions
                this.mejaData.forEach(meja => {
                    const x = meja.x;
                    const y = meja.y;
                    const size = 32;

                    // Draw meja shadow
                    this.ctx.shadowColor = 'rgba(0,0,0,0.1)';
                    this.ctx.shadowBlur = 8;
                    this.ctx.shadowOffsetX = 2;
                    this.ctx.shadowOffsetY = 2;

                    // Draw meja background
                    this.ctx.fillStyle = this.getStatusColor(meja.status);
                    this.ctx.beginPath();
                    this.ctx.roundRect(x, y, size, size, 8);
                    this.ctx.fill();

                    // Draw meja border
                    this.ctx.strokeStyle = this.getStatusBorderColor(meja.status);
                    this.ctx.lineWidth = 2;
                    this.ctx.beginPath();
                    this.ctx.roundRect(x, y, size, size, 8);
                    this.ctx.stroke();

                    // Draw meja number
                    this.ctx.fillStyle = '#14171B';
                    this.ctx.font = 'bold 14px Inter, sans-serif';
                    this.ctx.textAlign = 'center';
                    this.ctx.textBaseline = 'middle';
                    this.ctx.fillText(meja.nomor.toString(), x + size/2, y + size/2);

                    // Draw status indicator
                    this.ctx.beginPath();
                    this.ctx.arc(x + size - 6, y + size - 6, 6, 0, Math.PI * 2);
                    this.ctx.fillStyle = this.getStatusColor(meja.status);
                    this.ctx.fill();
                    this.ctx.strokeStyle = 'white';
                    this.ctx.lineWidth = 2;
                    this.ctx.stroke();

                    // Draw order indicator
                    if (meja.hasOrder) {
                        this.ctx.beginPath();
                        this.ctx.arc(x + 26, y + 6, 8, 0, Math.PI * 2);
                        this.ctx.fillStyle = '#D64545';
                        this.ctx.fill();
                    }

                    this.ctx.shadowColor = 'transparent';
                });
            },

            getStatusColor(status) {
                switch(status) {
                    case 'kosong': return '#F6F1E7';
                    case 'terisi': return '#D6F5E3';
                    case 'butuh-bayar': return '#FDE8E8';
                    case 'nonaktif': return '#E5E7EB';
                    default: return '#F6F1E7';
                }
            },

            getStatusBorderColor(status) {
                switch(status) {
                    case 'kosong': return '#4E9A51';
                    case 'terisi': return '#4FA8C9';
                    case 'butuh-bayar': return '#D64545';
                    case 'nonaktif': return '#9CA3AF';
                    default: return '#E5E7EB';
                }
            },

            onMouseDown(e) {
                if (!this.editMode) return;
                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                this.mejaData.forEach(meja => {
                    const size = 32;
                    if (x >= meja.x && x <= meja.x + size && y >= meja.y && y <= meja.y + size) {
                        this.isDragging = true;
                        this.draggedMejaId = meja.id;
                        this.dragOffsetX = x - meja.x;
                        this.dragOffsetY = y - meja.y;
                        this.canvas.style.cursor = 'grabbing';
                    }
                });
            },

            onMouseMove(e) {
                if (!this.isDragging || !this.draggedMejaId) return;
                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const meja = this.mejaData.find(m => m.id === this.draggedMejaId);
                if (meja) {
                    meja.x = Math.max(0, Math.min(this.canvas.width - 32, x - this.dragOffsetX));
                    meja.y = Math.max(0, Math.min(this.canvas.height - 32, y - this.dragOffsetY));
                    this.draw();
                }
            },

            onMouseUp(e) {
                if (!this.isDragging || !this.draggedMejaId) return;
                
                const meja = this.mejaData.find(m => m.id === this.draggedMejaId);
                if (meja) {
                    // Snap to grid
                    const gridSize = 40;
                    meja.x = Math.round(meja.x / gridSize) * gridSize;
                    meja.y = Math.round(meja.y / gridSize) * gridSize;
                    
                    // Ensure within bounds
                    meja.x = Math.max(0, Math.min(this.canvas.width - 32, meja.x));
                    meja.y = Math.max(0, Math.min(this.canvas.height - 32, meja.y));
                    
                    // Send to server
                    @this.endDrag(this.draggedMejaId, meja.x, meja.y);
                }

                this.isDragging = false;
                this.draggedMejaId = null;
                this.canvas.style.cursor = 'default';
                this.draw();
            },

            onTouchStart(e) {
                if (!this.editMode) return;
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousedown', {
                    clientX: touch.clientX,
                    clientY: touch.clientY,
                });
                this.onMouseDown(mouseEvent);
            },

            onTouchMove(e) {
                if (!this.isDragging) return;
                e.preventDefault();
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousemove', {
                    clientX: touch.clientX,
                    clientY: touch.clientY,
                });
                this.onMouseMove(mouseEvent);
            },

            onTouchEnd(e) {
                const mouseEvent = new MouseEvent('mouseup', {});
                this.onMouseUp(mouseEvent);
            },

            onMejaClick(mejaId) {
                if (!this.editMode) {
                    @this.createManualOrder(mejaId);
                }
            }
        }
    }
</script>
@endpush