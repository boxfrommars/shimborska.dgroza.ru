@extends('layout')

@section('content')
    <h2>Увековечение</h2>

    <div class="poem">
        <p>
            В орешнике под росами<br/>
            сердца их гулко бились,<br/>
            былинки прошлогодние<br/>
            в их волосы забились.
        </p>
        <p>
            Ласточкино сердце,<br/>
            смилуйся над ними.
        </p>
        <p>
            Причесывались после<br/>
            над прудом, тихим с вечера,<br/>
            к ним рыбы подплывали,<br/>
            мерцающе отсвечивая.
        </p>
        <p>
            Ласточкино сердце,<br/>
            смилуйся над ними.
        </p>
        <p>
            Вода деревья смешивала<br/>
            с остатками рассвета.<br/>
            Ласточка, пускай они<br/>
            навек запомнят это.
        </p>
        <p>
            Касатка, терний тучи,<br/>
            якорь поднебесья,<br/>
            Икар в обличье лучшем,<br/>
            фрак, вознесенный в небо,
        </p>
        <p>
            касатка, каллиграфия,<br/>
            ранне-птичья готика,<br/>
            стрелочка секундная,<br/>
            неба косоглазие,
        </p>
        <p>
            ласточка, беззвучье,<br/>
            неугомонный траур,<br/>
            ореол влюбленных,<br/>
            смилуйся над ними.
        </p>
        <p class="foot-note">Перевод Асара Эппеля</p>
    </div>
    <div class="poem" lang="pl">
        <h3>Upamiętnienie</h3>
        <p>
            Kochali się w leszczynie<br/>
            pod słońcami rosy,<br/>
            suchych liści i ziemi<br/>
            nabrali we włosy.
        </p>
        <p>
            Serce jaskółki<br/>
            zmiłuj się nad nimi.
        </p>
        <p>
            Uklękli nad jeziorem,<br/>
            wyczesali liście<br/>
            a ryby podpływały<br/>
            do brzegu gwiaździście.
        </p>
        <p>
            Serce jaskółki<br/>
            zmiłuj się nad nimi.
        </p>
        <p>
            Odbicia drzew dymiły<br/>
            na zdrobniałej fali.<br/>
            Jaskółko, spraw, by nigdy<br/>
            nie zapominali.
        </p>
        <p>
            Jaskółko, cierniu chmury,<br/>
            kotwico powietrza,<br/>
            ulepszony Ikarze,<br/>
            wniebowzięty fraku,
        </p>
        <p>
            jaskółko kaligrafio,<br/>
            wskazówko bez minut,<br/>
            wczesno-ptasi gotyku,<br/>
            zezie na niebiosach,
        </p>
        <p>
            jaskółko ciszo ostra,<br/>
            żałobo wesoła,<br/>
            aureolo kochanków,<br/>
            zmiłuj się nad nimi.
        </p>
    </div>
@endsection

@section('notes')
@endsection

@section('images')
    <div class="left-box" style="--illustration-offset: 379px;">
        <a href="/images/full/icarus.webp" data-illustration data-illustration-title="Падение Икара"
           title="Увеличить изображение" aria-label="Увеличить фреску «Падение Икара»" aria-describedby="icarus-image">
            <img id="icarus-image" alt="Античная фреска: крылатая фигура летит над морем, внизу видны лодки, люди и лежащее на берегу тело. В центре большой утраченный участок живописи." src="/images/icarus.webp" width="150" height="221"/>
        </a>
        <p>Падение&nbsp;Икара<br/>
            (40–79&nbsp;гг.&nbsp;н.&nbsp;э.)</p>
        <p>Фреска из&nbsp;Помпей</p>
        <p><a href="https://commons.wikimedia.org/wiki/File:The_Fall_of_Icarus,_fresco_from_Pompeii,_40-79_AD.png">Sofia&nbsp;Suli</a>,<br/>
            <a href="https://creativecommons.org/licenses/by-sa/4.0/">CC&nbsp;BY-SA&nbsp;4.0</a></p>
    </div>
@endsection
