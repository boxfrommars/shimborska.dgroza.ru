@extends('layout')

@section('content')
    <h2>Бал</h2>

    <div class="poem">
        <p>
            Покуда толком ничего не ясно, <br/>
            поскольку нет сигналов долетевших,
        </p>
        <p>
            пока Земля опять же не такая, <br/>
            как ближние и дальние планеты,
        </p>
        <p>
            покуда нет ни слуху и ни духу <br/>
            о прочих травах, предпочтенных ветром, <br/>
            о деревах других в коронах кроны, <br/>
            другом зверье, как наше, несомненном,
        </p>
        <p>
            покуда нету эха, кроме местных, <br/>
            которое умело б говорить слогами,
        </p>
        <p>
            покуда ничего не сообщалось <br/>
            о худших или лучших <a class="tonote" id="tonote001" href="#note001" role="doc-noteref">амадеях, <br/>
						платонах или эдисонах</a>,
        </p>
        <p>
            пока злодейства наши <br/>
            соперничают только меж собой,
        </p>
        <p>
            а приданное нам добросердечье <br/>
            ни на какое больше не похоже <br/>
            и хоть сомнительно, зато одно такое,
        </p>
        <p>
            а головы с невнятицей иллюзий — <br/>
            единственные, полные иллюзий,
        </p>
        <p>
            а вопли, что возносим к небосводу <br/>
            всего лишь вопли из-под сводов нёба, —
        </p>
        <p>
            мы мним себя гостями на танцульке <br/>
            особыми и отличенными, <br/>
            танцуем под музыку местного оркестрика, <br/>
            и пусть нам представляется, <br/>
            что этот бал один и есть такой;
        </p>
        <p>
            кому как – не знаю, <br/>
            а мне достаточно <br/>
            для счастья и для злосчастья
        </p>
        <p>
            тихое захолустье, <br/>
            где звезды говорят спокойной ночи, <br/>
            немногозначительно <br/>
            перемигиваясь <br/>
            по нашему поводу.
        </p>
        <p class="foot-note">
            Перевод Асара Эппеля
        </p>
    </div>

    <div class="poem" lang="pl">
        <h3>Bal</h3>

        <p>
            Dopóki nie wiadomo jeszcze nic pewnego,<br/>
            bo brak sygnałów, które by dobiegły,
        </p>
        <p>
            dopóki Ziemia wciąż jeszcze nie taka<br/>
            jak do tej pory bliższe i dalsze planety,
        </p>
        <p>
            dopóki ani widu ani słychu<br/>
            o innych trawach zaszczycanych wiatrem,<br/>
            o innych drzewach ukoronowanych,<br/>
            innych zwierzętach udowodnionych jak nasze,
        </p>
        <p>
            dopóki nie ma echa, oprócz tubylczego,<br/>
            które by potrafiło mówić sylabami,
        </p>
        <p>
            dopóki żadnych nowin<br/>
            o lepszych albo gorszych gdzieś mozartach,<br/>
            platonach czy edisonach,
        </p>
        <p>
            dopóki nasze zbrodnie<br/>
            rywalizować mogą tylko między sobą,
        </p>
        <p>
            dopóki nasza dobroć<br/>
            na razie do niczyjej jeszcze nie podobna<br/>
            i wyjątkowa nawet w niedoskonałości,
        </p>
        <p>
            dopóki nasze głowy pełne złudzeń<br/>
            uchodzą za jedyne głowy pełne złudzeń,
        </p>
        <p>
            dopóki tylko z naszych jak dotąd podniebień<br/>
            wzbijają się wniebogłosy —
        </p>
        <p>
            czujmy się gośćmi w tutejszej remizie<br/>
            osobliwymi i wyróżnionymi,<br/>
            tańczmy do taktu miejscowej kapeli<br/>
            i niech się nam wydaje,<br/>
            że to bal nad bale.
        </p>
        <p>
            Nie wiem jak komu —<br/>
            mnie to zupełnie wystarcza<br/>
            do szczęścia i do nieszczęścia:
        </p>
        <p>
            niepozorny zaścianek,<br/>
            gdzie gwiazdy mówią dobranoc<br/>
            i mrugają w jego stronę<br/>
            nieznacząco.
        </p>
    </div>
@endsection

@section('notes')
    <div class="note" id="note001" role="doc-footnote" tabindex="-1">
        <p>Йоганн Хризостом Вольфганг Теофил Моцарт (1756 — 1791) — величайший австрийский композитор, инструменталист и
            дирижёр.</p>
        <p>Томас Алва Эдисон (1847 — 1931) — всемирно известный американский изобретатель и предприниматель</p>
        <p>Платон (428 или 427 до н. э. — 348 или 347 до н. э.) — древнегреческий философ, ученик Сократа, учитель
            Аристотеля. <a class="note-backlink" href="#tonote001" aria-label="Вернуться к месту примечания">↩</a></p>
    </div>
@endsection

@section('images')
    <div class="left-box" style="margin-top:50px;">
        <img alt="Моцарт, Вольфганг Амадей" src="/images/mozart.jpg"/>
        <p>Вольфганг Амадей Моцарт, портрет</p>
        <p>Барбара Крафт (1819)</p>
    </div>
    <div class="left-box">
        <img alt="Платон" src="/images/plato.jpg"/>
        <p>Платон на фреске Рафаэля Санти (1509)</p>
    </div>
    <div class="left-box">
        <img alt="Томас Эдисон" src="/images/edison.jpg"/>
        <p>Томас Эдисон (1915)</p>
    </div>
@endsection
