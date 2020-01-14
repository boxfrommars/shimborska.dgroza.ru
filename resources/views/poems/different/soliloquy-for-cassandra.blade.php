@extends('layout')

@section('content')
    <h2>Монолог для Кассандры</h2>

    <div class="poem">
        <p>
            Это я, <span class="tonote" id="tonote001">Кассандра</span>. <br/>
            А это мой город под пеплом. <br/>
            А это мой посох и ленты жрицы. <br/>
            А это моя голова, переполненная сомнениями.
        </p>
        <p>
            Это правда, я победила. <br/>
            Моя правота права, как луна в полнолунье. <br/>
            Только с пророком, которому напрочь не верят, <br/>
            может случиться такое. <br/>
            Только с теми, которые вяло взялись за дело, <br/>
            и все могло сбыться так быстро, <br/>
            как будто и не было вовсе.
        </p>
        <p>
            Отчетливо помню, <br/>
            как люди, увидев меня, смолкали на полуслове. <br/>
            Смех обрывался. <br/>
            Руки теряли друг друга. <br/>
            Дети бежали к матери. <br/>
            Я даже не знала их тленных имен. <br/>
            А песенка эта о зеленом листике — <br/>
            никто ее не закончил при мне.
        </p>
        <p>
            Я их любила. <br/>
            Но со своей колокольни. <br/>
            Над жизнью. <br/>
            Из будущего. Где всегда пусто <br/>
            и откуда проще простого увидеть смерть. <br/>
            Я жалею, что мой голос был твердым. <br/>
            Посмотрите со звезд на себя, — я кричала, — <br/>
            посмотрите со звезд на себя. <br/>
            Они слушали и смотрели под ноги.
        </p>
        <p>
            Жили в жизни они. <br/>
            Большими ветрами гонимы.<br/>
            Предначертанно жили. <br/>
            От рожденья в прощальных телах. <br/>
            Но жила в них надежда какая-то влажная, <br/>
            мерцал огонек, утоляющий голод мерцаньем. <br/>
            Знали они, что такое минута, <br/>
            о, если хотя бы одна, хоть какая-нибудь, <br/>
            прежде чем —
        </p>
        <p>
            Вышло по-моему. <br/>
            Но из этого ничего не следует. <br/>
            А это только моя одежка, огнем опаленная. <br/>
            А это только мои пророческие лохмотья. <br/>
            Это только мое искривленное лицо. <br/>
            Лицо, которое не знало, что могло быть прекрасным.
        </p>
        <p class="foot-note">
            Перевод Виктора Коркия
        </p>
    </div>
    <div class="poem">
        <h3>Monolog dla Kasandry</h3>
        <p>
            To ja, Kasandra.<br/>
            A to jest moje miasto pod popiołem.<br/>
            A to jest moja laska i wstążki prorockie.<br/>
            A to jest moja głowa pełna wątpliwości.
        </p>
        <p>
            To prawda, tryumfuję.<br/>
            Moja racja aż łuną uderzyła w niebo.<br/>
            Tylko prorocy, którym się nie wierzy,<br/>
            mają takie widoki.<br/>
            Tylko ci, którzy źle zabrali się do rzeczy,<br/>
            i wszystko mogło spełnić się tak szybko,<br/>
            jakby nie było ich wcale.
        </p>
        <p>
            Wyraźnie teraz przypominam sobie,<br/>
            jak ludzie, widząc mnie, milkli wpół słowa.<br/>
            Rwał się śmiech.<br/>
            Rozpalały się ręce.<br/>
            Dzieci biegły do matki.<br/>
            Nawet nie znałam ich nietrwałych imion.<br/>
            A ta piosenka o zielonym listku —<br/>
            nikt jej nie kończył przy mnie.
        </p>
        <p>
            Kochałam ich.<br/>
            Ale kochałam z wysoka.<br/>
            Sponad życia.<br/>
            Z przyszłości. Gdzie zawsze jest pusto<br/>
            i skąd cóż łatwiejszego jak zobaczyć śmierć.<br/>
            Żałuję, że mój głos był twardy.<br/>
            Spójrzcie na siebie z gwiazd — wołałam —<br/>
            spójrzcie na siebie z gwiazd.<br/>
            Słyszeli i spuszczali oczy.
        </p>
        <p>
            Żyli w życiu.<br/>
            Podszyci wielkim wiatrem.<br/>
            Przesądzeni.<br/>
            Od urodzenia w pożegnalnych ciałach.<br/>
            Ale była w nich jakaś wilgotna nadzieja,<br/>
            własną migotliwością sycący się płomyk.<br/>
            Oni wiedzieli, co to takiego jest chwila,<br/>
            och bodaj jedna jakakolwiek<br/>
            zanim —<br/>
            Wyszło na moje.<br/>
            tylko że z tego nie wynika nic.<br/>
            A to jest moja szmatka ogniem osmalona.<br/>
            A to są moje prorockie rupiecie.<br/>
            A to jest moja wykrzywiona twarz.<br/>
            Twarz, która nie wiedziała, że mogła być piękna.
        </p>
    </div>
@endsection

@section('notes')
    <div class="note" id="note001">
        <p>Кассандра (др.-греч. Κασσάνδρα) — персонаж древнегреческой мифологии, дочь Приама и Гекубы. Большинство
            авторов описывает её как прорицательницу, предсказаниям которой никто не верил. Согласно Гомеру, она была
            красивейшей из дочерей Приама.</p>
    </div>
@endsection

@section('images')
    <div class="left-box" style="margin-top:50px;">
        <img alt="кассандра" src="/images/cassandra.jpg"/>
        <p>Кассандра (1898)</p>
        <p>Э. Де Морган</p>
    </div>
@endsection
