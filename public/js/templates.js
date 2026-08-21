/**
 * Report Templates
 * Save and reuse report templates for common items
 */

class TemplateManager {
    constructor() {
        this.templates = JSON.parse(localStorage.getItem('report_templates') || '[]');
    }

    showTemplatesModal() {
        let modal = document.getElementById('templates-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'templates-modal';
            modal.className = 'shortcuts-modal';
            document.body.appendChild(modal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.remove('active');
            });
        }

        modal.innerHTML = `
            <div class="shortcuts-content" style="max-width:500px">
                <div class="shortcuts-title">
                    <i class="bi bi-file-earmark-richtext"></i> Report Templates
                </div>

                <div style="margin-bottom:1rem">
                    <button onclick="templateManager.createTemplate()" 
                        style="width:100%;padding:0.75rem;border:2px dashed #e2e8f0;border-radius:12px;background:none;color:#64748b;cursor:pointer;font-size:0.875rem;font-weight:600;transition:all 0.2s;font-family:inherit"
                        onmouseover="this.style.borderColor='#0041C7';this.style.color='#0041C7'" 
                        onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#64748b'">
                        <i class="bi bi-plus-lg" style="margin-right:0.5rem"></i> Create New Template
                    </button>
                </div>

                <div style="display:flex;flex-direction:column;gap:0.75rem;max-height:400px;overflow-y:auto">
                    ${this.templates.length
                        ? this.templates.map((t, i) => `
                            <div class="template-card" style="display:flex;align-items:center;gap:0.75rem;padding:1rem;background:white;border:1px solid #e2e8f0;border-radius:12px;transition:all 0.2s"
                                onmouseover="this.style.borderColor='#0041C7'" onmouseout="this.style.borderColor='#e2e8f0'">
                                <div style="width:44px;height:44px;border-radius:10px;background:rgba(0,65,199,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="bi bi-file-earmark-text" style="color:#0041C7;font-size:1.125rem"></i>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div style="font-weight:600;color:#1e293b;font-size:0.9375rem">${t.name}</div>
                                    <div style="font-size:0.8125rem;color:#94a3b8;margin-top:0.25rem">${t.type === 'lost' ? 'Lost Item' : 'Found Item'} · ${t.category || 'Uncategorized'}</div>
                                </div>
                                <div style="display:flex;gap:0.375rem">
                                    <button onclick="templateManager.useTemplate(${i})" style="padding:0.375rem 0.75rem;background:#0041C7;color:white;border:none;border-radius:6px;font-size:0.8125rem;font-weight:600;cursor:pointer;font-family:inherit" title="Use">
                                        Use
                                    </button>
                                    <button onclick="templateManager.editTemplate(${i})" style="padding:0.375rem;background:none;color:#64748b;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="templateManager.deleteTemplate(${i})" style="padding:0.375rem;background:none;color:#ef4444;border:1px solid #fecaca;border-radius:6px;cursor:pointer" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `).join('')
                        : '<div style="text-align:center;padding:3rem;color:#94a3b8"><i class="bi bi-file-earmark-plus" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:0.5"></i>No templates yet.<br>Create one to save time on common reports!</div>'
                    }
                </div>

                <div style="margin-top:1.5rem;text-align:center">
                    <button onclick="document.getElementById('templates-modal').classList.remove('active')" 
                        style="padding:0.5rem 1.5rem;background:#e2e8f0;color:#475569;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-family:inherit">
                        Close
                    </button>
                </div>
            </div>
        `;
        modal.classList.add('active');
    }

    createTemplate() {
        const name = prompt('Template name:');
        if (!name) return;

        const type = prompt('Type (lost/found):', 'lost');
        if (!type) return;

        const category = prompt('Category:', '');
        const description = prompt('Default description:', '');

        const template = { name, type, category, description, createdAt: new Date().toISOString() };
        this.templates.push(template);
        this.save();
        this.showTemplatesModal();
        toast.success('Template created!');
    }

    useTemplate(index) {
        const t = this.templates[index];
        if (!t) return;

        // Pre-fill form if on the create page
        const typeSelect = document.querySelector('select[name="report_type"]');
        const categorySelect = document.querySelector('select[name="category_id"]');
        const descInput = document.querySelector('textarea[name="item_description"]');

        if (typeSelect) typeSelect.value = t.type;
        if (categorySelect && t.category) categorySelect.value = t.category;
        if (descInput) descInput.value = t.description || '';

        document.getElementById('templates-modal')?.classList.remove('active');
        toast.success('Template applied!');
    }

    editTemplate(index) {
        const t = this.templates[index];
        if (!t) return;

        const name = prompt('Template name:', t.name);
        if (name) t.name = name;

        const type = prompt('Type (lost/found):', t.type);
        if (type) t.type = type;

        const category = prompt('Category:', t.category || '');
        if (category !== null) t.category = category;

        const description = prompt('Default description:', t.description || '');
        if (description !== null) t.description = description;

        this.save();
        this.showTemplatesModal();
        toast.success('Template updated!');
    }

    deleteTemplate(index) {
        if (!confirm('Delete this template?')) return;
        this.templates.splice(index, 1);
        this.save();
        this.showTemplatesModal();
        toast.info('Template deleted');
    }

    save() {
        localStorage.setItem('report_templates', JSON.stringify(this.templates));
    }
}

window.templateManager = new TemplateManager();
