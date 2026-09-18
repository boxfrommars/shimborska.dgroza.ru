@extends('layout')

@section('content')
    <h2>Письма умерших</h2>

    <div class="poem">
        <p>
            Читаем письма умерших, как бессильные боги,<br/>
            и все-таки&nbsp;— боги, ибо знаем позднейшие даты.<br/>
            Знаем, какие долги не вернулись к заимодавцам<br/>
            и за кого поспешно повыходили вдовы.<br/>
            Бедные умершие, слепые умершие,<br/>
            обманываемые, ошибавшиеся, неуклюже предусмотрительные.<br/>
            Мы видим за их спинами перемигиванья и знаки.<br/>
            Улавливаем шуршанье уничтожаемых завещаний.<br/>
            Они ж перед нами, смешные, сидят, как на булках с маслом,<br/>
            либо вдогонку бросаются за шляпами, сдутыми ветром.<br/>
            Их скверный вкус, Бонапарт, пар, электричество,<br/>
            убийственные курации излечимых болезней,<br/>
            неумный апокалипсис по апостолу Иоанну,<br/>
            фальшивый рай на земле по епистолам Жан-Жака…<br/>
            Мы озираем в молчанье их пешки на шахматном поле,<br/>
            разве что продвинутые на какие-нибудь три клетки.<br/>
            Все, что ими предсказано, произошло иначе,<br/>
            или чуть-чуть иначе, что опять же иначе.<br/>
            Самые пытливые глядят нам в глаза доверчиво,<br/>
            ибо точно расчислили, что узрят в них совершенство.
        </p>
        <p class="foot-note">Перевод Асара Эппеля</p>
    </div>
    <div class="poem" lang="pl">
        <h3>Listy umarłych</h3>
        <p>
            Czytamy listy umarłych jak bezradni bogowie,<br/>
            ale jednak bogowie, bo znamy późniejsze daty.<br/>
            Wiemy, które pieniądze nie zostały oddane.<br/>
            Za kogo prędko za mąż powychodziły wdowy.<br/>
            Biedni umarli, zaślepieni umarli,<br/>
            oszukiwani, omylni, niezgrabnie zapobiegliwi.<br/>
            Widzimy miny i znaki robione za ich plecami.<br/>
            Łowimy uchem szelest dartych testamentów.<br/>
            Siedzą przed nami śmieszni jak na bułkach z masłem,<br/>
            albo rzucają się w pogoń za zwianymi z głów kapeluszami.<br/>
            Ich zły gust, Napoleon, para i elektryczność,<br/>
            ich zabójcze kuracje na uleczalne choroby,<br/>
            niemądra apokalipsa według świętego Jana,<br/>
            fałszywy raj na ziemi według Jana Jakuba…<br/>
            Obserwujemy w milczeniu ich pionki na szachownicy,<br/>
            tyle że przesunięte o trzy pola dalej.<br/>
            Wszystko, co przewidzieli, wypadło zupełnie inaczej,<br/>
            albo trochę inaczej, czyli także zupełnie inaczej.<br/>
            Najgorliwsi wpatrują się nam ufnie w oczy,<br/>
            bo wyszło im z rachunku, że ujrzą w nich doskonałość.
        </p>
    </div>
@endsection

@section('notes')
@endsection

@section('images')
    <div class="left-box" style="--illustration-offset: 84px;">
        <a href="/images/full/napoleon.webp" data-illustration data-illustration-title="Наполеон I, король Италии"
           title="Увеличить изображение" aria-label="Увеличить портрет Наполеона" aria-describedby="napoleon-image">
            <img id="napoleon-image" alt="Наполеон в зелёной мантии с золотой вышивкой и орденами, опирающийся левой рукой на корону." src="/images/napoleon.webp" width="150" height="202"/>
        </a>
        <p>Наполеон&nbsp;I, король Италии (1805)</p>
        <p>Андреа&nbsp;Аппиани</p>
    </div>
    <div class="left-box" style="--illustration-offset: 165px;">
        <a href="/images/full/rousseau.webp" data-illustration data-illustration-title="Портрет Жан-Жака Руссо"
           title="Увеличить изображение" aria-label="Увеличить портрет Руссо" aria-describedby="rousseau-image">
            <img id="rousseau-image" alt="Жан-Жак Руссо в седоватом парике, коричневом камзоле и белом шейном платке на тёмном фоне." src="/images/rousseau.webp" width="150" height="209"/>
        </a>
        <p>Жан-Жак&nbsp;Руссо (ок.&nbsp;1753)</p>
        <p>Морис&nbsp;Кантен де&nbsp;Латур</p>
    </div>
@endsection
