@extends('layout')

@section('content')
    <h2>Первая фотография Гитлера</h2>

    <div class="poem">
        <p>
            А кто этот бутуз, такой прелестный? <br/>
            Это ж малыш Адольф, чадо супругов Гитлер! <br/>
            Может быть, вырастет доктором юриспруденции? <br/>
            Или же в венской опере будет тенором? <br/>
            Чья это ручка, шейка, глазки, ушко, носик? <br/>
            Чей это будет животик, еще неизвестно: <br/>
            печатника, коммерсанта, врача, священника? <br/>
            Куда эти милые ножки, куда они доберутся? <br/>
            В садик, в школу, в контору, на свадьбу, <br/>
            может быть, даже с дочерью бургомистра?
        </p>
        <p>
            Лапушка, ангелочек, солнышко, крошка, <br/>
            когда на свет рождался год назад, <br/>
            на небе и земле не обошлось без знаков: <br/>
            весеннее солнце и герани в окнах <br/>
            и музыка шарманки во дворе, <br/>
            счастливая планета в розовой бумажке, <br/>
            а перед родами пророческий сон матери: <br/>
            голубя во сне видеть — радостная новость, <br/>
            поймать его же — прибудет гость долгожданный. <br/>
            Тук-тук, кто там, стучится будущий Адольфик.
        </p>
        <p>
            Пеленочка, слюнявчик, соска, погремушка, <br/>
            мальчонка, слава Богу и тьфу-тьфу, здоровый, <br/>
            похож на папу-маму, на котика в корзинке, <br/>
            на всех других детишек в семейных альбомах. <br/>
            Ну не будем же плакать, господин фотограф <br/>
            накроется черной накидкой и сделает: пстрык.
        </p>
        <p>
            Ателье Клингер, Грабенштрассе, <span class="tonote" id="tonote001">Браунау,</span><br/>
            а Браунау — город маленький, но достойный, <br/>
            почтенные соседи, солидные фирмы, <br/>
            дух дрожжевого теста и простого мыла. <br/>
            Не слышно ни воя собак, ни шагов судьбы. <br/>
            Учитель истории расстегивает воротничок <br/>
            и зевает.
        </p>
        <p class="foot-note">
            Перевод Натальи Астафьевой
        </p>
    </div>
    <div class="poem">
        <h3>Pierwsza fotografia Hitlera</h3>
        <p>
            A któż to jest ten mały dzidziuś w kaftaniku?<br/>
            Toż to mały Adolfek, syn państwa Hitlerów!<br/>
            Może wyrośnie na doktora praw?<br/>
            Albo będzie tenorem w operze wiedeńskiej?<br/>
            Czyja to rączka, czyja, uszko, oczko, nosek?<br/>
            Czyj brzuszek pełen mleka, nie wiadomo jeszcze:<br/>
            drukarza, konsyliarza, kupca, księdza?<br/>
            Dokąd te śmieszne nóżki zawędrują, dokąd?<br/>
            Do ogródka, do szkoły, do biura, na ślub<br/>
            może z córką burmistrza?
        </p>
        <p>
            Bobo, aniołek, kruszyna, promyczek,<br/>
            kiedy rok temu przychodził na świat,<br/>
            nie brakło znaków na niebie i ziemi:<br/>
            wiosenne słońce, w oknach pelargonie,<br/>
            muzyka katarynki na podwórku,<br/>
            pomyślna wróżba w bibułce różowej,<br/>
            tuż przed porodem proroczy sen matki:<br/>
            gołąbka we śnie widzieć - radosna nowina,<br/>
            tegoż schwytać - przybędzie gość długo czekany.<br/>
            Puk puk, kto tam, to stuka serduszko Adolfka.
        </p>
        <p>
            Smoczek, pieluszka, śliniaczek, grzechotka,<br/>
            chłopczyna, chwalić Boga i odpukać, zdrów,<br/>
            podobny do rodziców, do kotka w koszyku,<br/>
            do dzieci z wszystkich innych rodzinnych albumów.<br/>
            No, nie będziemy chyba teraz płakać,<br/>
            pan fotograf pod czarną płachtą zrobi pstryk.
        </p>
        <p>
            Atelier Klinger, Grabenstrasse Braunau,<br/>
            a Braunau to niewielkie, ale godne miasto,<br/>
            solidne firmy, poczciwi sąsiedzi,<br/>
            woń ciasta drożdżowego i szarego mydła.<br/>
            Nie słychać wycia psów i kroków przeznaczenia.<br/>
            Nauczyciel historii rozluźnia kołnierzyk<br/>
            i ziewa nad zeszytami.
        </p>
    </div>
@endsection

@section('notes')
    <div class="note" id="note001">
        <p>Браунау-на-Инне — город в&nbsp;Австрии, в&nbsp;федеральной земле Верхняя Австрия, в&nbsp;Браунау 20&nbsp;апреля&nbsp;1889
            года родился Адольф Гитлер</p>
    </div>
@endsection

@section('images')
    <div class="left-box" style="margin-top:50px;">
        <img alt="кассандра" src="/images/younghitler.jpg"/>
        <p>А. Гитлер, 12 лет (1901)</p>
        <p>Школьная фотография, г.&nbsp;Ленц</p>
    </div>
    <div class="left-box">
        <img alt="Мемориальный камень против войны и фашизма" src="/images/mahnstein.jpg"/>
        <p>Памятник перед домом, где родился Гитлер</p>
        <p>«За&nbsp;свободу, мир и&nbsp;демократию. Не&nbsp;допустим фашизма вновь, миллионы погибших просят нас об&nbsp;этом»</p>
    </div>
@endsection
