@extends('layout')

@section('content')
    <h2>Авторский вечер</h2>

    <div class="poem">
        <p>
            Муза, не быть боксером, значит вовсе не быть.<br/>
            Орущих залов ты нам пожалела.<br/>
            Двенадцать человек уже имеют быть,<br/>
            давно пора и вечер бы открыть.<br/>
            Пришли не все, поскольку дождь,<br/>
            прочие&nbsp;— родственники. Муза.
        </p>
        <p>
            Готовы женщины в осенний этот вечер<br/>
            попадать в обморок, но на боксерской встрече.<br/>
            Подмостки Данта только там.<br/>
            И приобщенье небу. Муза.
        </p>
        <p>
            Не быть боксером, быть поэтом,<br/>
            приговоренным на пожизненные строки,<br/>
            за неимением мускулатуры демонстрировать<br/>
            грядущее школьное чтение&nbsp;— удачу редких из нас&nbsp;—<br/>
            о Муза. О Пегас,<br/>
            ангел конноспортивный.
        </p>
        <p>
            В первом ряду старичок задремал и видит свою старушку,<br/>
            покойница встала из гроба испечь для него ватрушку, и<br/>
            ловко этак ватрушка эта поспела<br/>
            на огне, на маленьком, чтобы не подгорела,<br/>
            начинаем чтение. Муза.
        </p>
        <p class="foot-note">Перевод Асара Эппеля</p>
    </div>
    <div class="poem" lang="pl">
        <h3>Wieczór autorski</h3>
        <p>
            Muzo, nie być bokserem to jest nie być wcale.<br/>
            Ryczącej publiczności poskąpiłaś nam.<br/>
            Dwanaście osób jest na sali,<br/>
            już czas, żebyśmy zaczynali.<br/>
            Połowa przyszła, bo deszcz pada,<br/>
            reszta to krewni. Muzo.
        </p>
        <p>
            Kobiety rade zemdleć w ten jesienny wieczór,<br/>
            zrobią to, ale tylko na bokserskim meczu.<br/>
            Dantejskie sceny tylko tam.<br/>
            I wniebobranie. Muzo.
        </p>
        <p>
            Nie być bokserem, być poetą,<br/>
            mieć wyrok skazujący na ciężkie norwidy,<br/>
            z braku muskulatury demonstrować światu<br/>
            przyszłą lekturę szkolną&nbsp;– w najszczęśliwszym razie&nbsp;–<br/>
            o Muzo. O Pegazie,<br/>
            aniele koński.
        </p>
        <p>
            W pierwszym rządku staruszek słodko sobie śni,<br/>
            że mu żona nieboszczka z grobu wstała i<br/>
            upiecze staruszkowi placek ze śliwkami.<br/>
            Z ogniem, ale niewielkim, bo placek się spali,<br/>
            zaczynamy czytanie. Muzo.
        </p>
    </div>
@endsection

@section('notes')
@endsection

@section('images')
    <div class="left-box" style="--illustration-offset: 175px;">
        <a href="/images/full/pegasus.webp" data-illustration data-illustration-title="Скульптура Пегаса"
           title="Увеличить изображение" aria-label="Увеличить скульптуру Пегаса" aria-describedby="pegasus-image">
            <img id="pegasus-image" alt="Зеленоватая скульптура Пегаса: крылатый конь с приподнятым передним копытом стоит на крыше на фоне голубого неба." src="/images/pegasus.webp" width="150" height="107"/>
        </a>
        <p>Пегас на&nbsp;Большом театре им.&nbsp;Станислава Монюшко в&nbsp;Познани (1910)</p>
        <p>Генрих&nbsp;Дюлль, Георг&nbsp;Пецольд</p>
        <p>Фото:&nbsp;<a href="https://commons.wikimedia.org/wiki/File:2008-09_Pegasus.JPG">Ziko</a>, <a href="https://creativecommons.org/licenses/by-sa/3.0/">CC&nbsp;BY-SA&nbsp;3.0</a></p>
    </div>
@endsection
