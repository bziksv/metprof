<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сервис");
?>

<div class="mip-modules-container">
    <div class="mip-modules-header">
        <h2 class="mip-modules-title">Наши услуги</h2>
        <p class="mip-modules-subtitle">Комплексные решения для кровельных и фасадных работ от профессионалов</p>
    </div>
    
    <div class="mip-modules-grid">
        <!-- Модуль 1 -->
        <div class="mip-module-card mip-module-card--calculation">
            <div class="mip-module-card__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 7H11V9H9V7ZM9 11H11V13H9V11ZM9 15H11V17H9V15ZM13 7H15V9H13V7ZM13 11H15V13H13V11ZM13 15H15V17H13V15ZM5 3H19C20.1 3 21 3.9 21 5V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V5C3 3.9 3.9 3 5 3ZM5 5V19H19V5H5Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="mip-module-card__badge">Бесплатно</div>
            <h3 class="mip-module-card__title">Бесплатный расчёт</h3>
            <p class="mip-module-card__description">Наши сотрудники помогут правильно скомплектовать ваш заказ, избежать стандартных ошибок и как следствие сэкономить время и деньги.</p>
            <ul class="mip-module-card__features">
                <li class="mip-module-card__feature">Профессиональный подбор материалов</li>
                <li class="mip-module-card__feature">Оптимизация расходов</li>
                <li class="mip-module-card__feature">Детальная смета за 24 часа</li>
                <li class="mip-module-card__feature">Расчёт доставки на объект</li>
            </ul>
            <a style="color: #fff;" href="/service/raschet/" class="mip-module-card__link">Получить расчёт</a>
        </div>
        
        <!-- Модуль 2 -->
        <div class="mip-module-card mip-module-card--measurement">
            <div class="mip-module-card__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9C9.5 7.62 10.62 6.5 12 6.5C13.38 6.5 14.5 7.62 14.5 9C14.5 10.38 13.38 11.5 12 11.5Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="mip-module-card__badge">Бесплатно</div>
            <h3 class="mip-module-card__title">Бесплатный замер</h3>
            <p class="mip-module-card__description">Наши специалисты помогут избежать ошибок и произвести правильный замер кровли или фасада. Для этого мы осуществляем бесплатный выезд на ваш объект.</p>
            <ul class="mip-module-card__features">
                <li class="mip-module-card__feature">Выезд специалиста в течение</li>
                <li class="mip-module-card__feature">Точные замеры кровли и фасада</li>
                <li class="mip-module-card__feature">Консультация на объекте</li>
                <li class="mip-module-card__feature">Фотофиксация особенностей</li>
            </ul>
            <a style="color: #fff;" href="/service/zamer/" class="mip-module-card__link">Заказать выезд замерщика</a>
        </div>
        
        <!-- Модуль 3 -->
        <div class="mip-module-card mip-module-card--installation">
            <div class="mip-module-card__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM10 17H8V13H10V17ZM10 11H8V7H10V11ZM16 17H12V15H16V17ZM16 13H12V11H16V13ZM16 9H12V7H16V9Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="mip-module-card__badge">Гарантия</div>
            <h3 class="mip-module-card__title">Поиск монтажной бригады</h3>
            <p class="mip-module-card__description">Мы подберём для вас опытную монтажную бригаду, которая качественно выполнит работу «под ключ». Все работы выполняются с гарантией качества.</p>
            <ul class="mip-module-card__features">
                <li class="mip-module-card__feature">Проверенные сертифицированные бригады</li>
                <li class="mip-module-card__feature">Работа «под ключ» с гарантией</li>
                <li class="mip-module-card__feature">Официальный договор на все работы</li>
            </ul>
			<a style="color: #fff;" href="/service/montazh/" class="mip-module-card__link">Подобрать бригаду</a>
        </div>
    </div>
</div>

<style>
    /* Цветовая палитра на основе #0358a6 */
    :root {
        --mip-primary: #0358a6;
        --mip-primary-light: #2a78c8;
        --mip-primary-lighter: #4d94e0;
        --mip-primary-dark: #024a8a;
        --mip-primary-darker: #01396b;
        --mip-gray-dark: #2c3e50;
        --mip-gray: #5d6d7e;
        --mip-gray-light: #ecf0f1;
        --mip-gray-lighter: #f8fafc;
        --mip-white: #ffffff;
    }
    
    /* Контейнер модулей */
    .mip-modules-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 4rem;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    
    /* Заголовок секции */
    .mip-modules-header {
        text-align: center;
        margin-bottom: 3.5rem;
    }
    
    .mip-modules-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--mip-primary-darker);
        margin-bottom: 0.75rem;
        position: relative;
        display: inline-block;
    }
    
    .mip-modules-title:after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: linear-gradient(90deg, var(--mip-primary), var(--mip-primary-light));
        border-radius: 2px;
    }
    
    .mip-modules-subtitle {
        font-size: 1.125rem;
        color: var(--mip-gray);
        max-width: 700px;
        margin: 1.5rem auto 0;
        line-height: 1.6;
        font-weight: 400;
    }
    
    /* Сетка модулей */
    .mip-modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
    }
    
    /* Карточка модуля */
    .mip-module-card {
        background: var(--mip-white);
        border-radius: 16px;
        padding: 2.5rem 2rem;
        box-shadow: 0 10px 30px rgba(3, 88, 166, 0.08);
        transition: all 0.35s ease;
        border: 1px solid rgba(3, 88, 166, 0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .mip-module-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(3, 88, 166, 0.15);
        border-color: rgba(3, 88, 166, 0.2);
    }
    
    /* Бейдж */
    .mip-module-card__badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        background: rgba(3, 88, 166, 0.08);
        color: var(--mip-primary);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1.5rem;
        align-self: flex-start;
    }
    
    /* Иконка модуля */
    .mip-module-card__icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--mip-primary), var(--mip-primary-dark));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        color: white;
    }
    
    .mip-module-card--measurement .mip-module-card__icon {
        background: linear-gradient(135deg, var(--mip-primary-light), var(--mip-primary));
    }
    
    .mip-module-card--installation .mip-module-card__icon {
        background: linear-gradient(135deg, var(--mip-primary-darker), var(--mip-primary-dark));
    }
    
    /* Заголовок карточки */
    .mip-module-card__title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--mip-primary-darker);
        margin-bottom: 1.2rem;
        line-height: 1.3;
    }
    
    /* Описание */
    .mip-module-card__description {
        color: var(--mip-gray);
        margin-bottom: 1.8rem;
        line-height: 1.6;
        flex-grow: 1;
        font-size: 1rem;
        font-weight: 400;
    }
    
    /* Список особенностей */
    .mip-module-card__features {
        list-style: none;
        margin: 0 0 2.2rem 0;
        padding: 0;
    }
    
    .mip-module-card__feature {
        padding: 0.5rem 0;
        color: var(--mip-gray-dark);
        position: relative;
        padding-left: 1.75rem;
        line-height: 1.5;
        font-size: 0.95rem;
    }
    
    .mip-module-card__feature:before {
        content: '✓';
        position: absolute;
        left: 0;
        top: 0.5rem;
        width: 20px;
        height: 20px;
        background: rgba(3, 88, 166, 0.08);
        color: var(--mip-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    /* Ссылка-кнопка */
    .mip-module-card__link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, var(--mip-primary), var(--mip-primary-dark));
        color: #fff;
        border-radius: 10px;
        font-size: 1.05rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        margin-top: auto;
        letter-spacing: 0.5px;
        text-decoration: none;
        border: none;
    }
    
    .mip-module-card__link:hover {
        background: linear-gradient(135deg, var(--mip-primary-dark), var(--mip-primary-darker));
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(3, 88, 166, 0.25);
        text-decoration: none;
        color: white;
    }
    
    /* Градиентные обводки при наведении */
    .mip-module-card--calculation:hover {
        border-image: linear-gradient(135deg, var(--mip-primary), var(--mip-primary-light)) 1;
    }
    
    .mip-module-card--measurement:hover {
        border-image: linear-gradient(135deg, var(--mip-primary-light), var(--mip-primary)) 1;
    }
    
    .mip-module-card--installation:hover {
        border-image: linear-gradient(135deg, var(--mip-primary-darker), var(--mip-primary-dark)) 1;
    }
    
    /* Адаптивность */
    @media (max-width: 1024px) {
        .mip-modules-grid {
            gap: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .mip-modules-grid {
            grid-template-columns: 1fr;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .mip-modules-title {
            font-size: 2rem;
        }
        
        .mip-modules-container {
            padding: 3rem 1rem;
        }
        
        .mip-module-card {
            padding: 2rem 1.75rem;
        }
    }
    
    @media (max-width: 480px) {
        .mip-modules-container {
            padding: 2.5rem 0.75rem;
        }
        
        .mip-modules-title {
            font-size: 1.75rem;
        }
        
        .mip-modules-subtitle {
            font-size: 1rem;
            padding: 0 0.5rem;
        }
        
        .mip-module-card {
            padding: 1.75rem 1.5rem;
        }
        
        .mip-module-card__link {
            padding: 0.9rem 1.25rem;
            font-size: 1rem;
        }
    }
</style>




<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>