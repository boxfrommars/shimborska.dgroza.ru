@extends('layout')

@section('content')
    <h2>Надгробная надпись</h2>

    <div class="poem">
        <p>
            Здесь старомодная, как точка с запятой,<br/>
            лежит Шимборская. Прими и упокой,<br/>
            земля, писательницу, хоть сей труп<br/>
            считался вне литературных групп.<br/>
            Лопух, сова и это вот творенье&nbsp;—<br/>
            все, чем украшен камень непригожий.<br/>
            Вынь электронные мозги, прохожий,<br/>
            и о судьбе ее поразмышляй мгновенье.
        </p>
        <p class="foot-note">Перевод Асара Эппеля</p>
    </div>
    <div class="poem" lang="pl">
        <h3>Nagrobek</h3>
        <p>
            Tu leży staroświecka jak przecinek<br/>
            autorka paru wierszy. Wieczny odpoczynek<br/>
            raczyła dać jej ziemia, pomimo że trup<br/>
            nie należał do żadnej z literackich grup.<br/>
            Ale też nic lepszego nie ma na mogile<br/>
            oprócz tej rymowanki, łopianu i sowy.<br/>
            Przechodniu, wyjmij z teczki mózg elektronowy<br/>
            i nad losem Szymborskiej podumaj przez chwilę.
        </p>
    </div>
@endsection

@section('notes')
@endsection

@section('images')
    <div class="left-box" style="--illustration-offset: 105px;">
        <a href="/images/full/grave.webp" data-illustration data-illustration-title="Надгробие Виславы Шимборской"
           title="Увеличить изображение" aria-label="Увеличить фото надгробия Шимборской" aria-describedby="grave-image">
            <img id="grave-image" alt="Светлое каменное надгробие Виславы Шимборской с тёмной плитой «Wisława Szymborska 1923–2012», цветами, лампадками и белой фигуркой совы." src="/images/grave.webp" width="150" height="226"/>
        </a>
        <p>Надгробие Виславы&nbsp;Шимборской в&nbsp;Кракове</p>
        <p>Фото:&nbsp;<a href="https://commons.wikimedia.org/wiki/File:Warszawskie,_Krak%C3%B3w,_Poland_-_panoramio_(80).jpg">marek7400</a>, <a href="https://creativecommons.org/licenses/by/3.0/">CC&nbsp;BY&nbsp;3.0</a></p>
    </div>
@endsection
