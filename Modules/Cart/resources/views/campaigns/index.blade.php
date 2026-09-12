<x-app-layout>
    <div class="p-6 space-y-6" x-data="campaignManager()" x-init="load()">
        <div class="flex items-center justify-between"><div><h1 class="text-2xl font-bold">Campaigns</h1><p class="text-sm text-gray-500">Create product offers and manage their active dates.</p></div></div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Create form -->
            <form @submit.prevent="create()" class="bg-white rounded-xl border p-5 space-y-3">
                <h2 class="font-semibold">New campaign</h2>
                <input x-model="form.name" required placeholder="Campaign name" class="w-full rounded border-gray-300">
                <textarea x-model="form.description" placeholder="Description" class="w-full rounded border-gray-300"></textarea>
                <label class="block text-sm font-medium text-gray-700">Banner image <span class="font-normal text-gray-400">(JPG, PNG or WebP · max 5 MB)</span></label>
                <input @change="form.banner_image = $event.target.files[0]" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded border-gray-300">
                <div class="grid grid-cols-2 gap-2">
                    <input x-model="form.starts_at" type="datetime-local" class="rounded border-gray-300">
                    <input x-model="form.ends_at" type="datetime-local" class="rounded border-gray-300">
                </div>
                <select x-model="form.status" data-local-select2 class="w-full rounded border-gray-300">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                </select>
                <button class="px-4 py-2 rounded bg-primary text-white">Create campaign</button>
            </form>

            <!-- Campaign list -->
            <div class="lg:col-span-2 space-y-3">
                <template x-for="campaign in campaigns" :key="campaign.id">
                    <div class="bg-white border rounded-xl p-4">
                        <div class="flex justify-between items-start gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold" x-text="campaign.name"></p>
                                <p class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-x-1">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="campaign.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" x-text="campaign.is_active ? 'Active' : 'Inactive'"></span>
                                    <span>·</span><span x-text="campaign.products_count"></span> products
                                    <span>·</span><span class="capitalize" x-text="campaign.status ?? 'n/a'"></span>
                                    <template x-if="campaign.starts_at || campaign.ends_at">
                                        <span>· <span x-text="formatDate(campaign.starts_at)"></span><span x-show="campaign.ends_at"> → <span x-text="formatDate(campaign.ends_at)"></span></span></span>
                                    </template>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button @click="editCampaign(campaign)" class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-200 text-gray-600 hover:text-primary hover:border-primary transition">Edit</button>
                                <button @click="toggleActive(campaign.id)" class="px-3 py-1.5 rounded-lg text-sm font-medium border transition" :class="campaign.is_active ? 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100' : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100'"><span x-text="campaign.is_active ? 'Deactivate' : 'Activate'"></span></button>
                                <button @click="select(campaign.id)" class="text-primary text-sm">Manage products</button>
                            </div>
                        </div>
                    </div>
                </template>
                <p x-show="!campaigns.length" class="text-gray-500 text-sm">No campaigns yet.</p>
            </div>
        </div>

        <!-- Add products -->
        <div x-show="selected" class="bg-white rounded-xl border p-5 space-y-4">
            <h2 class="font-semibold">Add products to campaign</h2>
            <div class="flex gap-2">
                <input @input.debounce.300ms="searchProducts($event.target.value)" placeholder="Search product name..." class="flex-1 rounded border-gray-300">
                <select x-model="discount_type" data-local-select2 class="rounded border-gray-300"><option value="percentage">Percent</option><option value="fixed_amount">Fixed off</option><option value="fixed_price">Fixed price</option></select>
                <input x-model="discount_value" type="number" min="0" step="0.01" placeholder="Discount" class="w-28 rounded border-gray-300">
            </div>
            <template x-for="product in searchResults" :key="product.id">
                <div class="flex justify-between items-center border-t py-2"><span x-text="product.name"></span><button @click="addProduct(product.id)" class="text-primary text-sm">Add</button></div>
            </template>
            <template x-if="selectedData">
                <div class="border-t pt-3">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium">Included products</p>
                        <span class="text-xs text-gray-400">Drag <i class="fa-solid fa-grip-vertical"></i> to reorder</span>
                    </div>
                    <div id="includedProductsList" class="space-y-1">
                        <template x-for="item in selectedData.products" :key="item.id">
                            <div class="flex items-center justify-between gap-2 text-sm py-1.5 px-1 border rounded bg-white sortable-row" :data-id="item.id">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <i class="fa-solid fa-grip-vertical drag-handle text-gray-400 cursor-grab"></i>
                                    <template x-if="editingEntryId !== item.id">
                                        <span class="truncate" x-text="item.product.name + ' — ' + item.discount_value + ' ' + item.discount_type"></span>
                                    </template>
                                    <template x-if="editingEntryId === item.id">
                                        <div class="flex items-center gap-1 flex-1 min-w-0">
                                            <select x-model="editEntry.discount_type" data-local-select2 class="rounded border-gray-300 text-xs py-1">
                                                <option value="percentage">Percent</option>
                                                <option value="fixed_amount">Fixed off</option>
                                                <option value="fixed_price">Fixed price</option>
                                            </select>
                                            <input x-model="editEntry.discount_value" type="number" min="0" step="0.01" class="w-24 rounded border-gray-300 text-xs py-1">
                                            <button @click="editEntryCancel()" class="text-gray-400 text-xs">Cancel</button>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <template x-if="editingEntryId !== item.id">
                                        <button @click="editEntryStart(item)" class="text-blue-600 text-xs font-medium">Edit</button>
                                    </template>
                                    <template x-if="editingEntryId === item.id">
                                        <button @click="editEntrySave(item)" class="text-green-600 text-xs font-semibold">Save</button>
                                    </template>
                                    <button @click="removeProduct(item.id)" class="text-red-500 text-xs">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Edit campaign drawer (reuses x-drawer like other modules) -->
        <x-drawer id="campaignDrawer" overlayId="campaignOverlay" title="Edit Campaign" submitEntity="campaign" submitAction="save" :submit-btn-text="'Update Campaign'">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaign name</label>
                    <input x-model="editForm.name" type="text" class="w-full rounded border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="editForm.description" rows="2" class="w-full rounded border-gray-300"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Button text</label><input x-model="editForm.button_text" type="text" class="w-full rounded border-gray-300"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Priority</label><input x-model="editForm.priority" type="number" min="0" class="w-full rounded border-gray-300"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="editForm.status" data-local-select2 class="w-full rounded border-gray-300"><option value="draft">Draft</option><option value="active">Active</option><option value="paused">Paused</option></select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Starts at</label><input x-model="editForm.starts_at" type="datetime-local" class="w-full rounded border-gray-300"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Ends at</label><input x-model="editForm.ends_at" type="datetime-local" class="w-full rounded border-gray-300"></div>
                </div>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" x-model="editForm.is_featured" class="rounded border-gray-300"> Featured</label>
                    <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" x-model="editForm.is_active" class="rounded border-gray-300"> Active</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner image <span class="font-normal text-gray-400">(JPG, PNG or WebP · max 5 MB)</span></label>
                    <template x-if="editForm.banner_preview && !editForm.remove_banner">
                        <div class="flex items-center gap-3 mb-2">
                            <img :src="editForm.banner_preview" alt="Current banner" class="w-32 h-20 object-cover rounded border">
                            <button type="button" @click="removeBanner()" class="text-sm text-red-600 hover:text-red-700 font-medium">Remove banner</button>
                        </div>
                    </template>
                    <p x-show="editForm.remove_banner" class="text-sm text-amber-600 mb-2">This banner will be removed when you save.</p>
                    <input x-ref="bannerInput" :disabled="editForm.remove_banner" @change="editForm.banner_image = $event.target.files[0]; editForm.remove_banner = false;" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded border-gray-300">
                </div>
            </div>
        </x-drawer>
    </div>

    <!-- Sortable.js for drag-and-drop reorder -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        #includedProductsList .sortable-ghost { opacity: 0.4; background: #fef3c7; border: 1px dashed #f59e0b; }
        #includedProductsList .sortable-chosen { background: #eff6ff; }
        #includedProductsList .drag-handle { cursor: grab; }
    </style>

    <script>
        function campaignManager() {
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const request = (url, options={}) => { const isForm = options.body instanceof FormData; return fetch(url,{headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json',...(isForm ? {} : {'Content-Type':'application/json'})},...options}).then(r=>r.json()) };
            const toLocalInput = (iso) => { if(!iso) return ''; const d=new Date(iso); if(isNaN(d.getTime())) return ''; const p=n=>String(n).padStart(2,'0'); return d.getFullYear()+'-'+p(d.getMonth()+1)+'-'+p(d.getDate())+'T'+p(d.getHours())+':'+p(d.getMinutes()); };
            return {
                campaigns:[], selected:null, selectedData:null, searchResults:[], discount_type:'percentage', discount_value:'', editingEntryId:null, editEntry:{discount_type:'percentage',discount_value:''},
                form:{name:'',description:'',banner_image:null,starts_at:'',ends_at:'',status:'draft'},
                editForm:{id:null,name:'',description:'',button_text:'',priority:0,status:'draft',starts_at:'',ends_at:'',is_featured:false,is_active:true,banner_image:null,banner_preview:null},
                load(){ Crud.register('campaign','save',()=>this.saveEdit()); request('{{ route('campaigns.list') }}').then(d=>this.campaigns=d.data); },
                create(){ const body=new FormData(); Object.entries(this.form).forEach(([key,value])=>{ if(value!==null && value!=='') body.append(key,value) }); request('{{ route('campaigns.store') }}',{method:'POST',body}).then(()=>{ this.form={name:'',description:'',banner_image:null,starts_at:'',ends_at:'',status:'draft'}; this.load(); }); },
                toggleActive(id){ request('/campaigns/'+id+'/toggle-active',{method:'POST'}).then(()=>this.load()); },
                select(id){ this.selected=id; request('/campaigns/'+id).then(d=>{ this.selectedData=d.data; this.$nextTick(()=>this.initSortable()); }); },
                searchProducts(q){ if(q.length<2){this.searchResults=[]; return} request('{{ route('campaigns.products.search') }}?q='+encodeURIComponent(q)).then(d=>this.searchResults=d.data); },
                addProduct(product_id){ if(!this.discount_value) return; request('/campaigns/'+this.selected+'/products',{method:'POST',body:JSON.stringify({product_id,discount_type:this.discount_type,discount_value:this.discount_value})}).then(()=>this.select(this.selected)); },
                removeProduct(id){ request('/campaigns/'+this.selected+'/products/'+id,{method:'DELETE'}).then(()=>this.select(this.selected)); },
                editEntryStart(item){ this.editingEntryId=item.id; this.editEntry={discount_type:item.discount_type||'percentage',discount_value:String(item.discount_value??'')}; },
                editEntryCancel(){ this.editingEntryId=null; },
                editEntrySave(item){ if(!this.editingEntryId) return; request('/campaigns/'+this.selected+'/products/'+item.id,{method:'POST',body:JSON.stringify({_method:'PUT',discount_type:this.editEntry.discount_type,discount_value:this.editEntry.discount_value})}).then(()=>{ this.editingEntryId=null; this.select(this.selected); }); },
                initSortable(){ this.$nextTick(()=>{ const el=document.getElementById('includedProductsList'); if(!el || typeof Sortable==='undefined') return; if(el.__sortable) el.__sortable.destroy(); const vm=this; el.__sortable=Sortable.create(el,{animation:150,handle:'.drag-handle',ghostClass:'sortable-ghost',onEnd:function(){ const ids=Array.from(el.children).filter(c=>c.dataset && c.dataset.id).map(c=>parseInt(c.dataset.id,10)); if(!ids.length) return; request('/campaigns/'+vm.selected+'/products/reorder',{method:'POST',body:JSON.stringify({ordered_ids:ids})}).then(()=>vm.select(vm.selected)); }}); }); },
                formatDate(iso){ if(!iso) return ''; const d=new Date(iso); return isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); },
                editCampaign(campaign){ this.editForm={id:campaign.id,name:campaign.name??'',description:campaign.description??'',button_text:campaign.button_text??'',priority:campaign.priority??0,status:campaign.status??'draft',starts_at:toLocalInput(campaign.starts_at),ends_at:toLocalInput(campaign.ends_at),is_featured:!!campaign.is_featured,is_active:!!campaign.is_active,banner_image:null,banner_preview:(campaign.banner_image?('/storage/'+campaign.banner_image.replace(/^\//,'')):null),remove_banner:false}; this.$refs.bannerInput.value=''; if(window.openGlobalDrawer) openGlobalDrawer('campaignDrawer','campaignOverlay'); },
                removeBanner(){ this.editForm.remove_banner=true; this.editForm.banner_image=null; this.editForm.banner_preview=null; this.$refs.bannerInput.value=''; },
                saveEdit(){ if(!this.editForm.id) return; const body=new FormData(); ['name','description','button_text','status','priority'].forEach(k=>{ const v=this.editForm[k]; if(v!==null && v!=='') body.append(k,v); }); body.append('is_featured', this.editForm.is_featured?'1':'0'); body.append('is_active', this.editForm.is_active?'1':'0'); ['starts_at','ends_at'].forEach(k=>{ const v=this.editForm[k]; if(v!==null && v!=='') body.append(k,v); }); if(this.editForm.remove_banner) body.append('remove_banner','1'); if(this.editForm.banner_image) body.append('banner_image', this.editForm.banner_image); body.append('_method','PUT'); request('/campaigns/'+this.editForm.id,{method:'POST',body}).then(()=>{ if(window.closeGlobalDrawer) closeGlobalDrawer('campaignDrawer','campaignOverlay'); this.load(); }); }
            };
        }
    </script>
</x-app-layout>
