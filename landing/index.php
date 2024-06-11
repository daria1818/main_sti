<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');?>
<?
if($_GET['rel'] == 'MailingList' && isset($_GET['rel'])){?>
  <!DOCTYPE html>
  <html lang="ru">
  <head>
    <meta charset="utf-8">
    <title>STIOnline Umbrella</title>
    <meta name="description" content="S.T.I. Landing">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <meta property="og:image" content="images/distpromo_img">
    <link rel="stylesheet" type="text/css" href="assets/plugins/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/global.css">
    <script
    src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU="
    crossorigin="anonymous"></script>
    <script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(65657677, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true,
        trackHash:true,
        ecommerce:"dataLayer"
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/65657677" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
    <? $APPLICATION->ShowHead(); ?>
  </head>

  <body>
    <div id="panel"><?$APPLICATION->ShowPanel();?></div>
    <div class="wrapper">
      <header class="header">
    <div class="container">
      <div class="header-menu">
        <div class="header-logo">
          <div class="header__burger-wrap">
            <div class="header__burger">
              <span></span>
            </div>
          </div>
          <a class="header-logo__link" href="/">
            <img src="./assets/img/logo.header.svg" alt="logo" loading="lazy">
          </a>
          <p class="header-logo__caption">Депо для стоматологов <br> и зубных техников</p>
        </div>
        <div class="header-option">
          <div class="header-nav">
            <nav class="header-nav__menu">
              <ul class="header-nav__list">
                <li class="header-nav__item"><a class="header-nav__link js-scroll-to" href="#about">О Umbrella</a></li>
                <li class="header-nav__item"><a class="header-nav__link js-scroll-to" href="#methods">Способы
                    применения</a>
                </li>
                <li class="header-nav__item"><a class="header-nav__link js-scroll-to" href="#instruction">Инструкция</a>
                </li>
                <li class="header-nav__item"><a class="header-nav__link js-scroll-to" href="#product">Размеры
                    ретрактора</a>
                </li>
              </ul>
            </nav>
          </div>
          <div class="header-feedback-form">
            <button class="header-btn js-call-order" type="button" aria-label="request-call">
              <span>Заказать звонок</span>
              <svg class="icon call">
                <use href="./assets/svg/sprite.svg#call"></use>
              </svg>
            </button>
          </div>
        </div>
      </div>
      <div class="header-dd">
        <div class="container">
          <ul class="header-dd-list">
            <li class="header-dd-item"><a href="#about" class="header-dd-link js-scroll-to">О Umbrella</a>
            </li>
            <li class="header-dd-item"><a href="#methods" class="header-dd-link js-scroll-to">Способы
                применения</a></li>
            <li class="header-dd-item"><a href="#instruction" class="header-dd-link js-scroll-to">Инструкция</a>
            </li>
            <li class="header-dd-item"><a href="#product" class="header-dd-link js-scroll-to">Размеры
                ретрактора</a></li>
          </ul>
        </div>
      </div>
    </div>
  </header>

      <div class="popup js-app-form">
    <div class="popup-inner">
      <div class="popup-block">
        <div class="popup-header">
          <div class="popup-title">Заказать звонок</div>
        </div>
        <form action="send_webhoock.php" class="application-form js-form" method="POST" name="application-form" id="feedback-form">
          <div class="popup-box">
            <div class="popup-input-wrap form-group">
              <input type="text" class="popup-input form-control" placeholder="Ваше имя*" name="full-name" required=""
                data-pristine-required-message="необходимо заполнить поле">
            </div>

            <div class="popup-input-wrap form-group">
              <input type="tel" class="popup-input js-phone form-control" placeholder="Телефон*" name="phone" required=""
                data-pristine-required-message="необходимо заполнить поле" minlength="18">
            </div>

            <div class="popup-checkbox-wrap form-group">
              <input type="checkbox" class="popup-checkbox" id="privacy-policy" name="privacy-policy" required=""
                data-pristine-required-message="необходимо заполнить поле">
              <label for="privacy-policy">
                <span class="privacy-policy__text">
                  Я согласен на обработку персональных данных и ознакомился с
                  <a href="javascript:void(0);" class="privacy-policy__link" target="_blank">
                    политикой конфиденциальности</a>
                </span>
              </label>
            </div>
          </div>
          <button type="submit" name="submit" id="asd" class="btn btn-modal">Отправить заявку</button>
          <div id="msg"></div>
        </form>
      </div>
    </div>
    <div class="popup-close js-close">
      <svg class="icon icon-close">
        <use href="./assets/svg/sprite.svg#icon-close"></use>
      </svg>
    </div>
  </div>

      <main>
        <div class="container">
          <section class="banner">
            <div class="banner__info">
              <div class="banner__logo">
                <img src="./assets/img/logo.banner.svg" alt="logo" loading="lazy">
              </div>
              <div class="banner__box">
                <h2 class="banner__title">Практичная изоляция комфортным способом</h2>
                <a href="/catalog/otbelivanie-zubov/kabinetnoe-otbelivanie/zashchita_myagkikh_tkaney/" class="btn">Перейти в каталог</a>
              </div>

            </div>
            <div class="banner__img">
              <picture>
                <source srcset="./assets/img/banner.webp" type="image/webp">
                <img src="./assets/img/banner.png" alt="banner">
              </picture>
            </div>
          </section>

          <section class="about" id="about">
            <div class="about-inner">
              <div class="about-info">
                <h2 class="about-info__title">Свободный доступ для большинства процедур!</h2>
                <p class="about-info__text">
                  Umbrella — язычно, щёчный и губной ретрактор,
                  это новый стандарт комфорта для пациента и стабильный доступ для врача.
                </p>
                <p class="about-info__text">
                  Основная цель создания Umbrella — обеспечить комфорт для пациента во время стоматологических
                  вмешательств.
                </p>
              </div>
              <div class="about-video__box">
                <iframe class="about-video" src="https://www.youtube.com/embed/DCDkXkK-Dq0" title="YouTube video player"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowfullscreen>
                </iframe>
              </div>
            </div>
          </section>

          <section class="advantage">
            <div class="advantage-inner">
              <div class="advantage-box">
                <div class="advantage__item-big">
                  <h4 class="advantage__title">Идеальный</h4>
                  <p class="advantage__text">
                    Подходит для различных процедур: включая цифровое сканирование, реставрации по II классу, герметизация
                    фиссур, установка брекетов, отбеливание, профессиональная гигиена
                  </p>
                  <div class="advantage__img">
                    <picture>
                      <source srcset="./assets/img/img.webp" type="image/webp">
                      <img src="./assets/img/img.png" alt="Идельный . Фото">
                    </picture>
                  </div>
                </div>
                <div class="advantage__item-holder">
                  <div class="advantage__item">
                    <h4 class="advantage__title">Комфортный</h4>
                    <p class="advantage__text">
                      Позволяет пациенту свободно держать рот открытым, не растягивая губы, не опасаясь выскакивания
                      ретрактора и микротравм мягких тканей. Не вызывает рвотный рефлекс
                    </p>
                  </div>
                  <div class="advantage__item">
                    <h4 class="advantage__title">Удобный</h4>
                    <p class="advantage__text">
                      Легко размещается даже когда требуется регистрация прикуса, позволяя языку комфортно и безопасно
                      находится под контролем ретрактора
                    </p>
                  </div>
                </div>
              </div>
              <div class="advantage-container">
                <div class="advantage__item">
                  <h4 class="advantage__title">Облегчает работу</h4>
                  <p class="advantage__text">
                    Открывает пространство между губами и щекой. Это облегчает доступ для аспирационных систем
                  </p>
                </div>
                <div class="advantage__item">
                  <h4 class="advantage__title">Универсальный</h4>
                  <p class="advantage__text">
                    Подходит для пациентов с аллергией на латекс
                  </p>
                  <div class="advantage__img-mini">
                    <picture>
                      <source srcset="./assets/img/capli.webp" type="image/webp">
                      <img src="./assets/img/capli.png" alt="Подходит для пациентов с аллергией на латекс">
                    </picture>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="methods" id="methods">
            <h2 class="section-title">Способы применения</h2>

            <div class="methods__slider swiper">
              <div class="swiper-wrapper">
                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/methods-slider.webp" type="image/webp"> -->
                        <img src="./assets/img/f1.jpg" alt="Лучше визуализация при осмотре" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Лучше визуализация при осмотре</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/methods-slider.webp" type="image/webp"> -->
                        <img src="./assets/img/f2.jpg" alt="Контроль прикуса" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Контроль прикуса</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/methods-slider.webp" type="image/webp"> -->
                        <img src="./assets/img/f3.jpg" alt="Регистрация прикуса" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Регистрация прикуса</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                        <img src="./assets/img/f4.jpg" alt="Фиксация брекетов" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Фиксация брекетов</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                        <img src="./assets/img/f5.jpg" alt="Снятие оттиска с двух челюстей (снятие оттиска в прикусе)" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Снятие оттиска с двух челюстей (снятие оттиска в прикусе)</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                        <img src="./assets/img/f6.jpg" alt="Снятие оттиска" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Снятие оттиска</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                        <img src="./assets/img/f7.jpg" alt="Улучшенный доступ для препарирования" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Улучшенный доступ для препарирования</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                        <img src="./assets/img/f8.jpg" alt="Офисное отбеливание" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Офисное отбеливание</p>
                  </div>
                </div>

                <div class="swiper-slide">
                  <div class="methods-item">
                    <div class="methods-item__img">
                      <picture>
                        <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                        <img src="./assets/img/f9.jpg" alt="Непрямая фиксация брекетов" loading="lazy">
                      </picture>
                    </div>
                    <p class="methods-item__text">Непрямая фиксация брекетов</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="methods__slider-nav">

              <div class="swiper-prev nav-prev">
                <svg class="icon nav-btns">
                  <use href="./assets/svg/sprite.svg#nav-btns"></use>
                </svg>
              </div>

              <div class="swiper-next nav-next">
                <svg class="icon nav-btns">
                  <use href="./assets/svg/sprite.svg#nav-btns"></use>
                </svg>
              </div>

            </div>
          </section>

          <section class="instruction" id="instruction">
            <h2 class="section-title">Инструкция по установке Umbrella</h2>
            <div class="instruction-wrap">
              <div class="instruction__slider swiper">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <div class="instruction-item">
                      <div class="instruction-item__info">
                        <div class="instruction-item__title">Шаг 1</div>
                        <div class="instruction-item__text">
                          Одновременно сомкните верхний и нижний выступы ретрактора Umbrella, убедитесь, что стрелка
                          направлена строго вверх и все готово к установке. Не переворачивайте ретрактор
                        </div>
                      </div>
                      <div class="instruction-item__img">
                        <picture>
                          <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                          <img src="./assets/img/step1.jpg" alt="Шаг 1" loading="lazy">
                        </picture>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="instruction-item">
                      <div class="instruction-item__info">
                        <div class="instruction-item__title">Шаг 2</div>
                        <div class="instruction-item__text">
                          Попросите пациента разместить кончик языка и удерживать его у нёба
                        </div>
                      </div>
                      <div class="instruction-item__img">
                        <picture>
                          <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                          <img src="./assets/img/step2.jpg" alt="Шаг 2" loading="lazy">
                        </picture>
                      </div>
                    </div>
                  </div>

                  <div class="swiper-slide">
                    <div class="instruction-item">
                      <div class="instruction-item__info">
                        <div class="instruction-item__title">Шаг 3</div>
                        <div class="instruction-item__text">
                          Выберите любую сторону удобную вам и начните деликатно размещать ретрактор
                        </div>
                      </div>
                      <div class="instruction-item__img">
                        <picture>
                          <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                          <img src="./assets/img/step3.jpg" alt="Шаг 3">
                        </picture>
                      </div>
                    </div>
                  </div>

                  <div class="swiper-slide">
                    <div class="instruction-item">
                      <div class="instruction-item__info">
                        <div class="instruction-item__title">Шаг 4</div>
                        <div class="instruction-item__text">
                          Используйте выступы, чтобы отцентровать ретрактор во рту пациента
                        </div>
                      </div>
                      <div class="instruction-item__img">
                        <picture>
                          <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                          <img src="./assets/img/step4.jpg" alt="Шаг 4">
                        </picture>
                      </div>
                    </div>
                  </div>

                  <div class="swiper-slide">
                    <div class="instruction-item">
                      <div class="instruction-item__info">
                        <div class="instruction-item__title">Шаг 5</div>
                        <div class="instruction-item__text">
                          Убедитесь, что язык пациента комфортно лежит за блокатором языка и обеспечивает легкий доступ
                          Не располагайте блокатор поверх языка. Кончик языка должен быть поднят к нёбу
                        </div>
                      </div>
                      <div class="instruction-item__img">
                        <picture>
                          <!-- <source srcset="./assets/img/capa.webp" type="image/webp"> -->
                          <img src="./assets/img/step5.jpg" alt="Шаг 5">
                        </picture>
                      </div>
                    </div>
                  </div>


                </div>
              </div>
              <div class="instruction__slider-nav">
                <div class="swiper-prev nav-prev2">
                  <svg class="icon nav-btns2">
                    <use href="./assets/svg/sprite.svg#nav-btns2"></use>
                  </svg>
                </div>

                <div class="swiper-next nav-next2">
                  <svg class="icon nav-btns2">
                    <use href="./assets/svg/sprite.svg#nav-btns2"></use>
                  </svg>
                </div>
              </div>
              <div class="swiper-scrollbar"></div>
            </div>
          </section>

          <section class="product" id="product">
            <h2 class="section-title">Размеры ретрактора</h2>
            <div class="product-size">
              <div class="product-info">
                <h3 class="product-info__title">Как правильно выбрать размер ретрактора? </h3>
                <ul class="product-info__list">
                  <li>Если бы предполагалось использование слепочной ложки размера 2-3, то размер ретрактора Medium</li>
                  <li>Если бы предполагалось использование слепочных ложек размера от 4 до 6, то размер ретрактора Large
                  </li>
                  <li>В любой непонятной ситуации выбирайте размер Large</li>
                </ul>
              </div>
              <div class="product-img">
                <picture>
                  <source srcset="./assets/img/paketi.webp" type="image/webp">
                  <img src="./assets/img/paketi.jpg" alt="Как правильно выбрать размер ретрактора? Фото 1">
                </picture>
              </div>
            </div>
            <div class="product-table">
              <div class="product-table__img">
                <picture>
                  <source srcset="./assets/img/maski.webp" type="image/webp">
                  <img src="./assets/img/maski.jpg" alt="Как правильно выбрать размер ретрактора? Фото 2" loading="lazy">
                </picture>
              </div>

              <div class="product-table__block">

                <div class="product-table__header">
                  <div class="product-table__model">Модель</div>
                  <div class="product-table__quantity">Кол-во в упаковке</div>
                  <div class="product-table__size">Размер</div>
                </div>
                <div class="product-table__item">
                  <div class="product-table__model">4870-Umbrella Retractor Medium </div>
                  <div class="product-table__quantity">5pk</div>
                  <div class="product-table__size">Medium (средний)</div>
                </div>
                <div class="product-table__item">
                  <div class="product-table__model">4871-Umbrella Retractor Medium </div>
                  <div class="product-table__quantity">20pk</div>
                  <div class="product-table__size">Medium (средний)</div>
                </div>
                <div class="product-table__item">
                  <div class="product-table__model">5162-Umbrella Retractor Medium </div>
                  <div class="product-table__quantity">40pk</div>
                  <div class="product-table__size">Medium (средний)</div>
                </div>
                <div class="product-table__item">
                  <div class="product-table__model">5256-Umbrella Retractor Large </div>
                  <div class="product-table__quantity">5pk</div>
                  <div class="product-table__size">Large (большой)</div>
                </div>
                <div class="product-table__item">
                  <div class="product-table__model">5257-Umbrella Retractor Large </div>
                  <div class="product-table__quantity">20pk</div>
                  <div class="product-table__size">Large (большой)</div>
                </div>
                <div class="product-table__item">
                  <div class="product-table__model">5258-Umbrella Retractor Large </div>
                  <div class="product-table__quantity">40pk</div>
                  <div class="product-table__size">Large (большой)</div>
                </div>
              </div>
            </div>

            <div class="product-subtitle">Закажите Umbrella Retractor прямо сейчас по привлекательной цене</div>
            <a href="/catalog/otbelivanie-zubov/kabinetnoe-otbelivanie/zashchita_myagkikh_tkaney/" class="btn product-btn">Перейти в каталог</a>
          </section>
        </div>
      </main>
      <footer class="footer">
    <div class="container">
      <div class="footer-inner">
        <div class="footer-holder">
          <div class="footer-logo">
            <a class="header-logo__link" href="/">
              <svg class="icon logo-footer">
                <use href="./assets/svg/sprite.svg#logo-footer"></use>
              </svg>
            </a>
            <p class="footer-logo__caption">Депо для стоматологов <br> и зубных техников</p>
          </div>
          <div class="footer-info">
            <div class="footer-adress">
              <p>125130, г. Москва,</p>
              <p>Ул. Выборгская, д. 22,стр 2</p>
            </div>
            <div class="footer-connection">
              <a rel="nofollow" href="tel:+74951183787">+7 495 118-37-87</a>
              <a rel="nofollow" href="tel:+78005554607">+7 800 555-46-07</a>
              <p>Для звонков по России</p>
            </div>
          </div>
        </div>
        <div class="footer-box">
          <div class="footer-nav">
            <nav class="footer-nav__menu">
              <ul class="footer-nav__list">
                <li class="footer-nav__item"><a class="footer-nav__link js-scroll-to " href="#about">О Umbrella</a></li>
                <li class="footer-nav__item"><a class="footer-nav__link js-scroll-to " href="#methods">Способы
                    применения</a>
                </li>
                <li class="footer-nav__item"><a class="footer-nav__link js-scroll-to" href="#instruction">Инструкция</a>
                </li>
                <li class="footer-nav__item"><a class="footer-nav__link js-scroll-to" href="#product">Размеры
                    ретрактора</a>
                </li>
              </ul>
            </nav>
          </div>
          <div class="footer-container">
            <div class="footer-policy">
              <a href="javascript:void(0);" class="footer-policy__link" target="_blank">Политика конфиденциальности</a>
              <a href="/include/licenses_detail.php   " class="footer-policy__link" target="_blank">
                Политика обработки персональных данных
              </a>
            </div>
            <div class="footer-copyright">
              <a href="/">2023 © STI Online - интернет-магазин</a>
              <a href="https://r-top.ru/" rel="nofollow">Разработка R-top</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>

      <div class="overlay"></div>
    </div>
    <script defer src="./assets/plugins/swiper/swiper-bundle.min.js"></script>
    <script defer src="./assets/plugins/pristine/pristine.min.js"></script>
    <script defer src="./js/main.js"></script>
  </body>
  </html>
<?}else{
  header("Location: /");
  die();
}