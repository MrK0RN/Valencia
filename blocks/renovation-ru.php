<!-- Hero Section -->
<section class="content-section hero-surface">
    <div class="content-container">
        <div class="content-hero-card" style="background: linear-gradient(270deg, rgba(0, 0, 0, 0.25) 0%, rgba(0, 0, 0, 0.78) 100%), url('/assets/img/wp14994047-valencia-4k-wallpapers.png'); background-size: cover; background-position: center;">
            <div class="content-pill">Ремонт в Валенсии</div>
            <h1 style="color: #fff; font-size: clamp(28px, 4vw, 42px); font-weight: 800; line-height: 1.2; margin: 0;">Качественный ремонт квартир и домов в Валенсии</h1>
            <p class="content-lead">Полный цикл ремонтных работ — от дизайн-проекта до финальной уборки. Европейское качество материалов, прозрачные цены и строгое соблюдение сроков.</p>
            <div style="display: flex; gap: 24px; flex-wrap: wrap; margin-top: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 20px; height: 20px; filter: brightness(0) invert(1);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <span style="font-weight: 600;">Гарантия 2 года</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 20px; height: 20px; filter: brightness(0) invert(1);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <span style="font-weight: 600;">Фиксированная цена</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <img src="/assets/icons/renovation.svg" alt="" style="width: 20px; height: 20px; filter: brightness(0) invert(1);">
                    </div>
                    <span style="font-weight: 600;">Опытные мастера</span>
                </div>
            </div>
            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 16px;">
                <a href="#calculator" class="btn btn-large" style="text-decoration: none;">Рассчитать стоимость</a>
                <a href="#callback" class="btn btn-large" style="background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.4); text-decoration: none;">Получить консультацию</a>
            </div>
        </div>
    </div>
</section>

<!-- Calculator Section -->
<section class="content-section" id="calculator">
    <div class="content-container">
        <div class="section-title" style="color: #60724F; text-align: center; margin-bottom: 8px;">Онлайн калькулятор ремонта</div>
        <div class="section-subtitle" style="text-align: center; margin-bottom: 32px;">
            Рассчитайте предварительную стоимость ремонта за несколько кликов
        </div>
        
        <div class="calc-wrapper" style="background: #fff; border-radius: 24px; padding: clamp(24px, 4vw, 40px); box-shadow: 0 8px 32px rgba(0,0,0,0.08); border: 1px solid #e7ebdf;">
            <!-- Property Type Tabs -->
            <div class="calc-label" style="font-weight: 600; color: #333; margin-bottom: 12px;">Выберите тип помещения</div>
            <div class="calc-tabs" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px;">
                <button type="button" class="calc-tab active" data-tab="room" style="padding: 12px 24px; border-radius: 100px; border: 2px solid #60724F; background: #60724F; color: #fff; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Комната</button>
                <button type="button" class="calc-tab" data-tab="apartment" style="padding: 12px 24px; border-radius: 100px; border: 2px solid #60724F; background: transparent; color: #60724F; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Квартира</button>
                <button type="button" class="calc-tab" data-tab="newbuild" style="padding: 12px 24px; border-radius: 100px; border: 2px solid #60724F; background: transparent; color: #60724F; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Новострой</button>
            </div>

            <!-- Room Type Panel -->
            <div class="calc-panel active" id="panel-room">
                <div class="calc-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="calc-field">
                        <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Тип комнаты</label>
                        <select id="room-type" class="calc-select" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #ddd; background: #f8f9f5; font-size: 16px; color: #333; cursor: pointer;">
                            <option value="living" data-mult="1">Жилая комната</option>
                            <option value="kitchen" data-mult="1.2">Кухня</option>
                            <option value="bathroom" data-mult="1.5">Ванная комната</option>
                            <option value="toilet" data-mult="1.3">Туалет</option>
                            <option value="combined" data-mult="1.6">Совмещённый санузел</option>
                        </select>
                    </div>
                    <div class="calc-field">
                        <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Площадь, м²</label>
                        <input type="number" id="room-area" value="15" min="1" max="500" class="calc-input" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #ddd; background: #f8f9f5; font-size: 16px; color: #333;">
                    </div>
                </div>
            </div>

            <!-- Apartment Panel -->
            <div class="calc-panel" id="panel-apartment" style="display: none;">
                <div class="calc-field" style="margin-bottom: 16px;">
                    <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 12px;">Количество комнат</label>
                    <div class="calc-rooms" style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="calc-room-btn active" data-rooms="0" data-area="35" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #60724F; background: #60724F; color: #fff; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Студия</button>
                        <button type="button" class="calc-room-btn" data-rooms="1" data-area="45" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">1</button>
                        <button type="button" class="calc-room-btn" data-rooms="2" data-area="65" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">2</button>
                        <button type="button" class="calc-room-btn" data-rooms="3" data-area="85" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">3</button>
                        <button type="button" class="calc-room-btn" data-rooms="4" data-area="110" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">4</button>
                        <button type="button" class="calc-room-btn" data-rooms="5" data-area="140" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">5+</button>
                    </div>
                </div>
                <div class="calc-field">
                    <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Площадь, м²</label>
                    <input type="number" id="apartment-area" value="65" min="20" max="1000" class="calc-input" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #ddd; background: #f8f9f5; font-size: 16px; color: #333;">
                </div>
            </div>

            <!-- New Build Panel -->
            <div class="calc-panel" id="panel-newbuild" style="display: none;">
                <div class="calc-field" style="margin-bottom: 16px;">
                    <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 12px;">Количество комнат</label>
                    <div class="calc-rooms-new" style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="calc-room-btn-new active" data-rooms="0" data-area="40" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #60724F; background: #60724F; color: #fff; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Студия</button>
                        <button type="button" class="calc-room-btn-new" data-rooms="1" data-area="50" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">1</button>
                        <button type="button" class="calc-room-btn-new" data-rooms="2" data-area="70" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">2</button>
                        <button type="button" class="calc-room-btn-new" data-rooms="3" data-area="95" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">3</button>
                        <button type="button" class="calc-room-btn-new" data-rooms="4" data-area="120" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">4</button>
                        <button type="button" class="calc-room-btn-new" data-rooms="5" data-area="150" style="padding: 10px 18px; border-radius: 100px; border: 2px solid #ddd; background: transparent; color: #333; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">5+</button>
                    </div>
                </div>
                <div class="calc-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="calc-field">
                        <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Площадь, м²</label>
                        <input type="number" id="newbuild-area" value="70" min="20" max="1000" class="calc-input" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #ddd; background: #f8f9f5; font-size: 16px; color: #333;">
                    </div>
                    <div class="calc-field">
                        <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Состояние</label>
                        <select id="newbuild-state" class="calc-select" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #ddd; background: #f8f9f5; font-size: 16px; color: #333; cursor: pointer;">
                            <option value="finished" data-mult="0.8">С отделкой от застройщика</option>
                            <option value="rough" data-mult="1">С черновой отделкой</option>
                            <option value="shell" data-mult="1.2">Без отделки</option>
                            <option value="openplan" data-mult="1.4">Свободная планировка</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Renovation Type (common for all) -->
            <div class="calc-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px; align-items: start;">
                <div class="calc-field">
                    <label class="calc-label" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Тип ремонта</label>
                    <select id="renovation-type" class="calc-select" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #ddd; background: #f8f9f5; font-size: 16px; color: #333; cursor: pointer;">
                        <option value="cosmetic" data-price="150" data-materials="50" data-days="0.3">Косметический ремонт</option>
                        <option value="capital" data-price="350" data-materials="120" data-days="0.6">Капитальный ремонт</option>
                        <option value="euro" data-price="500" data-materials="180" data-days="1">Ремонт под ключ</option>
                    </select>
                </div>
                <div class="calc-description" style="background: #f8f9f5; border-radius: 12px; padding: 16px; border: 1px solid #e7ebdf;">
                    <p id="renovation-desc" style="margin: 0; color: #555; font-size: 14px; line-height: 1.5;"><strong>Косметический ремонт</strong> — обновление отделки без капитальных изменений: покраска, замена напольного покрытия, обновление потолков.</p>
                </div>
            </div>

            <!-- Results -->
            <div class="calc-results" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 32px; padding-top: 32px; border-top: 1px solid #e7ebdf;">
                <div class="calc-result-item" style="text-align: center; padding: 20px; background: linear-gradient(135deg, rgba(96, 114, 79, 0.05) 0%, rgba(96, 114, 79, 0.1) 100%); border-radius: 16px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 8px;">Стоимость ремонта</div>
                    <div id="result-cost" style="font-size: clamp(24px, 3vw, 32px); font-weight: 800; color: #60724F;">2 250 EUR</div>
                </div>
                <div class="calc-result-item" style="text-align: center; padding: 20px; background: linear-gradient(135deg, rgba(96, 114, 79, 0.05) 0%, rgba(96, 114, 79, 0.1) 100%); border-radius: 16px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 8px;">Стоимость материалов</div>
                    <div id="result-materials" style="font-size: clamp(24px, 3vw, 32px); font-weight: 800; color: #60724F;">750 EUR</div>
                </div>
                <div class="calc-result-item" style="text-align: center; padding: 20px; background: linear-gradient(135deg, rgba(96, 114, 79, 0.05) 0%, rgba(96, 114, 79, 0.1) 100%); border-radius: 16px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 8px;">Сроки ремонта</div>
                    <div id="result-days" style="font-size: clamp(24px, 3vw, 32px); font-weight: 800; color: #60724F;">5 дней</div>
                </div>
            </div>

            <p style="text-align: center; color: #888; font-size: 13px; margin-top: 16px;">* Расчёт приблизительный. Для точной оценки оставьте заявку на бесплатный замер.</p>

            <!-- CTA Form -->
            <div class="calc-cta" style="display: flex; gap: 16px; margin-top: 24px; flex-wrap: wrap;">
                <input type="tel" id="calc-phone" placeholder="Ваш телефон" style="flex: 1; min-width: 200px; padding: 16px 20px; border-radius: 100px; border: 1px solid #ddd; background: #f8f9f5; font-size: 16px;">
                <button type="button" id="calc-submit" class="btn btn-large" style="white-space: nowrap;">Заказать ремонт</button>
            </div>
        </div>
    </div>
</section>

<style>
/* Calculator responsive styles */
@media (max-width: 768px) {
    .calc-row {
        grid-template-columns: 1fr !important;
    }
    .calc-results {
        grid-template-columns: 1fr !important;
    }
    .calc-tabs {
        flex-direction: column;
    }
    .calc-tabs .calc-tab {
        width: 100%;
        text-align: center;
    }
    .calc-cta {
        flex-direction: column;
    }
    .calc-cta input,
    .calc-cta button {
        width: 100%;
    }
}

.calc-tab:hover:not(.active) {
    background: rgba(96, 114, 79, 0.1);
}

.calc-room-btn:hover:not(.active),
.calc-room-btn-new:hover:not(.active) {
    border-color: #60724F;
    color: #60724F;
}

.calc-select:focus,
.calc-input:focus {
    outline: none;
    border-color: #60724F;
    box-shadow: 0 0 0 3px rgba(96, 114, 79, 0.15);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Renovation type descriptions
    const descriptions = {
        cosmetic: '<strong>Косметический ремонт</strong> — обновление отделки без капитальных изменений: покраска, замена напольного покрытия, обновление потолков.',
        capital: '<strong>Капитальный ремонт</strong> — полная замена коммуникаций, выравнивание стен и полов, новая электрика и сантехника.',
        euro: '<strong>Ремонт под ключ</strong> — комплексная отделка от демонтажа до меблировки, включая дизайн-проект и все виды работ.'
    };

    // Tab switching
    const tabs = document.querySelectorAll('.calc-tab');
    const panels = document.querySelectorAll('.calc-panel');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => {
                t.classList.remove('active');
                t.style.background = 'transparent';
                t.style.color = '#60724F';
            });
            this.classList.add('active');
            this.style.background = '#60724F';
            this.style.color = '#fff';
            
            panels.forEach(p => p.style.display = 'none');
            document.getElementById('panel-' + this.dataset.tab).style.display = 'block';
            
            calculateCost();
        });
    });

    // Room buttons for apartment
    const roomBtns = document.querySelectorAll('.calc-room-btn');
    roomBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            roomBtns.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'transparent';
                b.style.color = '#333';
                b.style.borderColor = '#ddd';
            });
            this.classList.add('active');
            this.style.background = '#60724F';
            this.style.color = '#fff';
            this.style.borderColor = '#60724F';
            
            document.getElementById('apartment-area').value = this.dataset.area;
            calculateCost();
        });
    });

    // Room buttons for newbuild
    const roomBtnsNew = document.querySelectorAll('.calc-room-btn-new');
    roomBtnsNew.forEach(btn => {
        btn.addEventListener('click', function() {
            roomBtnsNew.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'transparent';
                b.style.color = '#333';
                b.style.borderColor = '#ddd';
            });
            this.classList.add('active');
            this.style.background = '#60724F';
            this.style.color = '#fff';
            this.style.borderColor = '#60724F';
            
            document.getElementById('newbuild-area').value = this.dataset.area;
            calculateCost();
        });
    });

    // Renovation type description update
    const renovationType = document.getElementById('renovation-type');
    renovationType.addEventListener('change', function() {
        document.getElementById('renovation-desc').innerHTML = descriptions[this.value];
        calculateCost();
    });

    // Calculate cost function
    function calculateCost() {
        const activeTab = document.querySelector('.calc-tab.active').dataset.tab;
        const renovationSelect = document.getElementById('renovation-type');
        const selectedOption = renovationSelect.options[renovationSelect.selectedIndex];
        
        const basePrice = parseFloat(selectedOption.dataset.price);
        const baseMaterials = parseFloat(selectedOption.dataset.materials);
        const baseDays = parseFloat(selectedOption.dataset.days);
        
        let area, multiplier = 1;
        
        if (activeTab === 'room') {
            area = parseFloat(document.getElementById('room-area').value) || 15;
            const roomType = document.getElementById('room-type');
            multiplier = parseFloat(roomType.options[roomType.selectedIndex].dataset.mult);
        } else if (activeTab === 'apartment') {
            area = parseFloat(document.getElementById('apartment-area').value) || 65;
            multiplier = 1.1; // Apartment coefficient
        } else {
            area = parseFloat(document.getElementById('newbuild-area').value) || 70;
            const newbuildState = document.getElementById('newbuild-state');
            multiplier = parseFloat(newbuildState.options[newbuildState.selectedIndex].dataset.mult);
        }
        
        const totalCost = Math.round(area * basePrice * multiplier);
        const totalMaterials = Math.round(area * baseMaterials * multiplier);
        const totalDays = Math.max(3, Math.round(area * baseDays));
        
        document.getElementById('result-cost').textContent = totalCost.toLocaleString('ru-RU') + ' EUR';
        document.getElementById('result-materials').textContent = totalMaterials.toLocaleString('ru-RU') + ' EUR';
        document.getElementById('result-days').textContent = totalDays + ' дней';
    }

    // Add event listeners for inputs
    document.getElementById('room-area').addEventListener('input', calculateCost);
    document.getElementById('room-type').addEventListener('change', calculateCost);
    document.getElementById('apartment-area').addEventListener('input', calculateCost);
    document.getElementById('newbuild-area').addEventListener('input', calculateCost);
    document.getElementById('newbuild-state').addEventListener('change', calculateCost);

    // Initial calculation
    calculateCost();

    // Submit button
    document.getElementById('calc-submit').addEventListener('click', function() {
        const phone = document.getElementById('calc-phone').value;
        if (!phone) {
            alert('Пожалуйста, введите номер телефона');
            return;
        }
        // Scroll to contact form or show success message
        const callback = document.getElementById('callback');
        if (callback) {
            callback.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>

<!-- Services Section -->
<section class="content-section">
    <div class="content-container">
        <div class="section-title" style="color: #60724F; text-align: center; margin-bottom: 8px;">Наши услуги</div>
        <div class="section-subtitle" style="text-align: center; margin-bottom: 24px;">
            Выполняем все виды ремонтных работ — от косметического обновления до полной реконструкции
        </div>
        <div class="content-grid">
            <div class="content-card">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img src="/assets/icons/plan.svg" alt="" style="width: 22px; height: 22px; filter: brightness(0) invert(1);">
                    </div>
                    <h3 style="margin: 0;">Дизайн интерьера</h3>
                </div>
                <p>Разработка индивидуального дизайн-проекта с 3D-визуализацией. Подбор материалов, мебели и декора в едином стиле.</p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(96, 114, 79, 0.15);">
                    <span style="color: #60724F; font-weight: 700;">от 25 EUR/м²</span>
                </div>
            </div>
            <div class="content-card">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img src="/assets/icons/renovation.svg" alt="" style="width: 22px; height: 22px; filter: brightness(0) invert(1);">
                    </div>
                    <h3 style="margin: 0;">Косметический ремонт</h3>
                </div>
                <p>Обновление отделки без капитальных изменений: покраска стен, замена напольного покрытия, обновление потолков.</p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(96, 114, 79, 0.15);">
                    <span style="color: #60724F; font-weight: 700;">от 150 EUR/м²</span>
                </div>
            </div>
            <div class="content-card">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img src="/assets/icons/commissioning.svg" alt="" style="width: 22px; height: 22px; filter: brightness(0) invert(1);">
                    </div>
                    <h3 style="margin: 0;">Капитальный ремонт</h3>
                </div>
                <p>Полная замена коммуникаций, перепланировка, выравнивание стен и полов, новая электрика и сантехника.</p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(96, 114, 79, 0.15);">
                    <span style="color: #60724F; font-weight: 700;">от 350 EUR/м²</span>
                </div>
            </div>
            <div class="content-card">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 22px; height: 22px; filter: brightness(0) invert(1);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <h3 style="margin: 0;">Ремонт «под ключ»</h3>
                </div>
                <p>Комплексное решение: от проекта до меблировки. Вы получаете полностью готовое к проживанию жильё.</p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(96, 114, 79, 0.15);">
                    <span style="color: #60724F; font-weight: 700;">от 500 EUR/м²</span>
                </div>
            </div>
            <div class="content-card">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 22px; height: 22px; filter: brightness(0) invert(1);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
                    </div>
                    <h3 style="margin: 0;">Сантехнические работы</h3>
                </div>
                <p>Монтаж и замена труб, установка сантехники, подключение бытовой техники, ремонт ванных комнат.</p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(96, 114, 79, 0.15);">
                    <span style="color: #60724F; font-weight: 700;">по запросу</span>
                </div>
            </div>
            <div class="content-card">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 22px; height: 22px; filter: brightness(0) invert(1);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <h3 style="margin: 0;">Электромонтаж</h3>
                </div>
                <p>Полная замена электропроводки, установка розеток и выключателей, монтаж освещения, умный дом.</p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(96, 114, 79, 0.15);">
                    <span style="color: #60724F; font-weight: 700;">по запросу</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Object Types Section -->
<section class="content-section muted">
    <div class="content-container">
        <div class="section-title" style="color: #60724F; text-align: center; margin-bottom: 8px;">Работаем с любыми объектами</div>
        <div class="section-subtitle" style="text-align: center; margin-bottom: 24px;">
            Опыт работы с различными типами недвижимости в регионе Валенсии
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div class="object-card" style="background: #fff; border-radius: 16px; padding: 24px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e7ebdf; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <svg style="width: 32px; height: 32px; color: #fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                </div>
                <h4 style="margin: 0 0 8px; color: #333;">Квартиры</h4>
                <p style="margin: 0; color: #666; font-size: 14px;">Студии, апартаменты, пентхаусы любой площади</p>
            </div>
            <div class="object-card" style="background: #fff; border-radius: 16px; padding: 24px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e7ebdf; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <svg style="width: 32px; height: 32px; color: #fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h4 style="margin: 0 0 8px; color: #333;">Виллы и дома</h4>
                <p style="margin: 0; color: #666; font-size: 14px;">Частные дома, таунхаусы, виллы с бассейном</p>
            </div>
            <div class="object-card" style="background: #fff; border-radius: 16px; padding: 24px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e7ebdf; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <svg style="width: 32px; height: 32px; color: #fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <h4 style="margin: 0 0 8px; color: #333;">Коммерция</h4>
                <p style="margin: 0; color: #666; font-size: 14px;">Офисы, магазины, рестораны, отели</p>
            </div>
            <div class="object-card" style="background: #fff; border-radius: 16px; padding: 24px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e7ebdf; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <svg style="width: 32px; height: 32px; color: #fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                </div>
                <h4 style="margin: 0 0 8px; color: #333;">Новостройки</h4>
                <p style="margin: 0; color: #666; font-size: 14px;">Отделка с нуля в новых жилых комплексах</p>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="content-section">
    <div class="content-container">
        <div class="section-title" style="color: #60724F; text-align: center; margin-bottom: 8px;">Почему выбирают нас</div>
        <div class="section-subtitle" style="text-align: center; margin-bottom: 24px;">
            Работаем по европейским стандартам качества с полной прозрачностью на каждом этапе
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <div class="benefit-card" style="background: linear-gradient(135deg, rgba(96, 114, 79, 0.03) 0%, rgba(96, 114, 79, 0.08) 100%); border-radius: 20px; padding: 24px; border: 1px solid rgba(96, 114, 79, 0.15);">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                    <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 26px; height: 26px; color: #fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <h4 style="margin: 0; font-size: 18px; font-weight: 700; color: #333;">Прозрачные цены</h4>
                </div>
                <p style="margin: 0; color: #555; line-height: 1.6;">Фиксируем стоимость в договоре. Детальная смета без скрытых платежей. Вы точно знаете, за что платите на каждом этапе.</p>
            </div>
            <div class="benefit-card" style="background: linear-gradient(135deg, rgba(96, 114, 79, 0.03) 0%, rgba(96, 114, 79, 0.08) 100%); border-radius: 20px; padding: 24px; border: 1px solid rgba(96, 114, 79, 0.15);">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                    <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 26px; height: 26px; color: #fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <h4 style="margin: 0; font-size: 18px; font-weight: 700; color: #333;">Соблюдение сроков</h4>
                </div>
                <p style="margin: 0; color: #555; line-height: 1.6;">Прописываем сроки в договоре и несём финансовую ответственность за их нарушение. Регулярные отчёты о ходе работ.</p>
            </div>
            <div class="benefit-card" style="background: linear-gradient(135deg, rgba(96, 114, 79, 0.03) 0%, rgba(96, 114, 79, 0.08) 100%); border-radius: 20px; padding: 24px; border: 1px solid rgba(96, 114, 79, 0.15);">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                    <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 26px; height: 26px; color: #fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h4 style="margin: 0; font-size: 18px; font-weight: 700; color: #333;">Гарантия 2 года</h4>
                </div>
                <p style="margin: 0; color: #555; line-height: 1.6;">Предоставляем письменную гарантию на все выполненные работы. Бесплатно устраняем любые дефекты в гарантийный период.</p>
            </div>
        </div>
    </div>
</section>

<!-- Steps Section - Animated Timeline -->
<section class="content-section muted" id="process-section">
    <div class="content-container">
        <div class="section-title" style="color: #60724F; text-align: center; margin-bottom: 8px;">Как мы работаем</div>
        <div class="section-subtitle" style="text-align: center; margin-bottom: 48px;">
            Простой и понятный процесс от первого звонка до передачи готового объекта
        </div>
        
        <div class="process-timeline">
            <div class="process-step" data-step="1">
                <div class="process-step-marker">
                    <span class="process-step-number">1</span>
                </div>
                <div class="process-step-content">
                    <span class="process-step-badge">1-2 дня</span>
                    <h4 class="process-step-title">Консультация и осмотр объекта</h4>
                    <p class="process-step-desc">Бесплатный выезд специалиста для оценки объёма работ. Обсуждаем ваши пожелания, измеряем помещения, фиксируем текущее состояние.</p>
                </div>
            </div>
            
            <div class="process-step" data-step="2">
                <div class="process-step-marker">
                    <span class="process-step-number">2</span>
                </div>
                <div class="process-step-content">
                    <span class="process-step-badge">2-3 дня</span>
                    <h4 class="process-step-title">Составление сметы</h4>
                    <p class="process-step-desc">Готовим детальную смету с разбивкой по видам работ и материалам. Обсуждаем варианты оптимизации бюджета без потери качества.</p>
                </div>
            </div>
            
            <div class="process-step" data-step="3">
                <div class="process-step-marker">
                    <span class="process-step-number">3</span>
                </div>
                <div class="process-step-content">
                    <span class="process-step-badge">1 день</span>
                    <h4 class="process-step-title">Заключение договора</h4>
                    <p class="process-step-desc">Подписываем официальный договор с фиксированной ценой и сроками. Все условия прозрачны — никаких скрытых платежей.</p>
                </div>
            </div>
            
            <div class="process-step" data-step="4">
                <div class="process-step-marker">
                    <span class="process-step-number">4</span>
                </div>
                <div class="process-step-content">
                    <span class="process-step-badge">по плану</span>
                    <h4 class="process-step-title">Выполнение работ</h4>
                    <p class="process-step-desc">Опытная бригада приступает к работе. Регулярно отправляем фото- и видеоотчёты. Вы можете контролировать процесс дистанционно.</p>
                </div>
            </div>
            
            <div class="process-step" data-step="5">
                <div class="process-step-marker">
                    <span class="process-step-number">5</span>
                </div>
                <div class="process-step-content">
                    <span class="process-step-badge">финал</span>
                    <h4 class="process-step-title">Приёмка и гарантия</h4>
                    <p class="process-step-desc">Совместная приёмка работ, устранение замечаний, финальная уборка. Передаём гарантийный сертификат на 2 года.</p>
                </div>
            </div>
        </div>
        
        <!-- Result Card -->
        <div class="process-result">
            <div class="process-result-card">
                <div class="process-result-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="process-result-content">
                    <span class="process-result-label">Итог</span>
                    <p class="process-result-title">Готовый ремонт с гарантией 2 года</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Process Timeline Styles */
.process-timeline {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
    padding-left: 80px;
}

/* Vertical line */
.process-timeline::before {
    content: '';
    position: absolute;
    left: 28px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #e7ebdf 0%, #60724F 20%, #60724F 80%, #e7ebdf 100%);
    border-radius: 2px;
}

.process-step {
    position: relative;
    padding-bottom: 40px;
}

.process-step:last-child {
    padding-bottom: 0;
}

/* Step marker */
.process-step-marker {
    position: absolute;
    left: -52px;
    top: 0;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 3px solid #60724F;
    border-radius: 50%;
    z-index: 2;
    box-shadow: 0 4px 12px rgba(96, 114, 79, 0.15);
    transition: all 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}

.process-step-number {
    font-size: 22px;
    font-weight: 800;
    color: #60724F;
    transition: color 0.5s ease;
}

/* Step content */
.process-step-content {
    background: #fff;
    border-radius: 20px;
    padding: 24px 28px;
    border: 1px solid #e7ebdf;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    transform: translateX(0);
}

.process-step-badge {
    display: inline-block;
    padding: 6px 14px;
    background: linear-gradient(135deg, rgba(96, 114, 79, 0.1) 0%, rgba(96, 114, 79, 0.2) 100%);
    border-radius: 100px;
    font-size: 13px;
    font-weight: 700;
    color: #60724F;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.process-step-title {
    margin: 0 0 10px;
    font-size: 20px;
    font-weight: 700;
    color: #333;
}

.process-step-desc {
    margin: 0;
    font-size: 15px;
    color: #666;
    line-height: 1.6;
}

/* Expanded state */
.process-step.is-expanded .process-step-marker {
    background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%);
    border-color: #60724F;
    transform: scale(1.15);
    box-shadow: 0 8px 24px rgba(96, 114, 79, 0.35);
}

.process-step.is-expanded .process-step-number {
    color: #fff;
}

.process-step.is-expanded .process-step-content {
    transform: translateX(20px);
    border-color: rgba(96, 114, 79, 0.3);
    box-shadow: 0 12px 32px rgba(96, 114, 79, 0.12);
    background: linear-gradient(135deg, #fff 0%, #f8faf5 100%);
}

/* Hover states */
.process-step:hover .process-step-marker {
    transform: scale(1.08);
}

.process-step:hover .process-step-content {
    border-color: rgba(96, 114, 79, 0.25);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

/* Result card */
.process-result {
    margin-top: 48px;
    text-align: center;
}

.process-result-card {
    display: inline-flex;
    align-items: center;
    gap: 20px;
    padding: 20px 32px;
    background: linear-gradient(135deg, #60724F 0%, #7a8f63 100%);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(96, 114, 79, 0.35);
}

.process-result-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 14px;
}

.process-result-icon svg {
    width: 28px;
    height: 28px;
    color: #fff;
}

.process-result-content {
    text-align: left;
}

.process-result-label {
    display: block;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.process-result-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
}

/* Mobile styles */
@media (max-width: 768px) {
    .process-timeline {
        padding-left: 60px;
    }
    
    .process-timeline::before {
        left: 20px;
    }
    
    .process-step-marker {
        left: -40px;
        width: 44px;
        height: 44px;
    }
    
    .process-step-number {
        font-size: 18px;
    }
    
    .process-step-content {
        padding: 20px;
    }
    
    .process-step.is-expanded .process-step-content {
        transform: translateX(0);
    }
    
    .process-step-title {
        font-size: 17px;
    }
    
    .process-step-desc {
        font-size: 14px;
    }
    
    .process-result-card {
        flex-direction: column;
        gap: 16px;
        padding: 24px;
    }
    
    .process-result-content {
        text-align: center;
    }
    
    .process-result-title {
        font-size: 18px;
    }
}

@media (max-width: 480px) {
    .process-timeline {
        padding-left: 50px;
    }
    
    .process-timeline::before {
        left: 16px;
    }
    
    .process-step-marker {
        left: -34px;
        width: 36px;
        height: 36px;
    }
    
    .process-step-number {
        font-size: 16px;
    }
    
    .process-step-content {
        padding: 16px;
    }
    
    .process-step-badge {
        font-size: 11px;
        padding: 4px 10px;
    }
    
    .process-step-title {
        font-size: 16px;
    }
    
    .process-step-desc {
        font-size: 13px;
    }
}
</style>

<script>
// Process steps animation on scroll
(function() {
    const processSteps = document.querySelectorAll('.process-step');
    
    if (processSteps.length === 0) return;
    
    const observerOptions = {
        root: null,
        rootMargin: '-30% 0px -30% 0px',
        threshold: 0
    };
    
    const stepObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-expanded');
            } else {
                entry.target.classList.remove('is-expanded');
            }
        });
    }, observerOptions);
    
    processSteps.forEach(step => {
        stepObserver.observe(step);
    });
})();
</script>

<!-- CTA Section -->
<section class="content-section hero-surface">
    <div class="content-container">
        <div class="content-hero-card" style="text-align: center; align-items: center;">
            <div class="content-pill">Начните сегодня</div>
            <h2 style="color: #fff; font-size: clamp(24px, 3.5vw, 36px); font-weight: 800; line-height: 1.3; margin: 0;">Готовы обсудить ваш проект?</h2>
            <p class="content-lead" style="text-align: center; max-width: 700px;">Оставьте заявку на бесплатную консультацию. Мы свяжемся с вами в течение нескольких часов, чтобы обсудить детали и назначить осмотр объекта.</p>
            <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; margin-top: 8px;">
                <a href="#callback" class="btn btn-large" style="text-decoration: none;">Получить консультацию</a>
                <a href="tel:+34744644228" class="btn btn-large" style="background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.4); text-decoration: none;">+34 744 644 228</a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<?php include __DIR__ . '/contact-ru.php'; ?>
