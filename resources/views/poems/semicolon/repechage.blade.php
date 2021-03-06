@extends('layout')

@section('content')
    <h2>Утешение</h2>

    <div class="poem">
        <p>
            <span class="tonote" id="tonote001">Дарвин.</span><br/>
            Для отдохновения якобы читал романы. <br/>
            Однако терпеть не мог, <br/>
            если они кончались печально. <br/>
            Когда ему такой попадался, <br/>
            яростно швырял его в камин.
        </p>
        <p>
            Правда, неправда – <br/>
            а я склонна верить.
        </p>
        <p>
            Обозрев разумом столько пространств и времен, <br/>
            наглядевшись стольких вымерших видов, <br/>
            таких триумфов сильных над слабыми, <br/>
            такой уймы попыток просуществовать, <br/>
            уже с начала или в результате напрасных, <br/>
            что хотя бы от вымысла <br/>
            с его микромасштабом <br/>
            имел право ожидать хеппи-энда.
        </p>
        <p>
            То есть обязательно: лучик из-за туч, <br/>
            любовники снова вместе, семьи помирились, <br/>
            сомнения рассеяны, верность вознаграждена, <br/>
            ценности откопаны, состояния возвращены, <br/>
            соседи сожалеют о своем ожесточении, <br/>
            доброе имя восстановлено, алчность устыжена, <br/>
            старые девы выданы за почтенных пасторов, <br/>
            интриганы сосланы в другое полушарие, <br/>
            подделыватели документов спущены с лестниц, <br/>
            соблазнители девиц спешат к алтарям, <br/>
            вдовы утешены, сироты не брошены, <br/>
            гордыня смирена, раны обихожены, <br/>
            блудные сыновья позваны к столу, <br/>
            горькая чаша выплеснута в море, <br/>
            платки мокры от слез примиренья, <br/>
            повсюду ликованье – музыка и пенье, <br/>
            а собачка Фидо, <br/>
            потерявшаяся еще в первой главе, <br/>
            пускай опять бегает по дому <br/>
            и радостно тявкает.
        </p>
        <p class="foot-note">
            Перевод Асара Эппеля
        </p>
    </div>
@endsection

@section('notes')
    <div class="note" id="note001">
        <p>
            Чарлз Роберт Дарвин (1809&nbsp;—&nbsp;1882)&nbsp;— английский натуралист и&nbsp;путешественник, одним из&nbsp;первых
            осознал и&nbsp;наглядно продемонстрировал, что все живые организмы эволюционируют во&nbsp;времени от&nbsp;общих
            предков.
        </p>
    </div>
@endsection

@section('images')
    <div class="left-box" style="margin-top:50px;">
        <img alt="Чарлз Дарвин" src="/images/darwin.jpg"/>
        <p>Чарлз Дарвин (1881)</p>
    </div>
@endsection
