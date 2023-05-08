@extends('layouts.app')

@section('site_title', formatTitle([__('Chat'), config('settings.title')]))
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="bg-base-1 flex-fill">
    <div class="container py-3 my-3">
        <div class="row">
            <div class="col-12">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                    ['url' => request()->is('admin/*') ?
                    route('admin.dashboard') : route('dashboard'), 'title' =>
                    request()->is('admin/*') ? __('Admin') : __('Home')],
                    ['title' => __('Chat')],
                    ]])
                    <div class="d-flex align-items-end mb-3">
                        <h1 class="h2 mb-3 text-break">{{ __('Chat with Livia')
                            }}</h1>
                    </div>
                </div>
                <div class="col-12" id="main" style="display: flex">
                    <div class="col-12" style="height: 20vh">
                        <div id="chat-area" class="chat-area">
                            @if ($errors->has('name'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- <div class="col-3"> -->
                        <div id="mySidebar" class="right-side">
                            <div class="buttongroup">
                                <button class="openbtn" style="display: block"
                                    id="view-temp-btn"
                                    onclick="viewTemplate(0)">☰</button>
                            </div>
                            <div class="sidebarBody">
                                <div style="display:inline-block; color:white">
                                    Recent
                                </div>
                                <div id="recent-topic">
                                    <!-- recent topic lists here -->
                                </div>
                            </div>
                        </div>
                    <!-- </div> -->
                </div>
            </div>
        </div>
        <div class="row sidebar sidebar-footer">
            <div class="col-12" style="position: absolute; width: 70%; bottom: 30px;">
                <div class="generating-animation" style="display: none;" id="loadingId">
                    <div class="loader">
                        <div class="loader--dot"></div>
                        <div class="loader--dot"></div>
                        <div class="loader--dot"></div>
                        <div class="loader--dot"></div>
                        <div class="loader--dot"></div>
                        <div class="loader--text"></div>
                    </div>
                </div>
                <form enctype="multipart/form-data" autocomplete="off"
                    id="form-templates-ask" onsubmit="event.preventDefault();"
                    class="mr-1">
                    <input type="hidden" name="_token" id="_token"
                        value="pCLNDUyTeuViLrudFf4jBWrHcWZaPbBBCLINmrWn">
                    <div class="input-group input-group-lg">
                        <input type="text" name="ask" id="ask" value="Hiking is good for health?"
                            class="form-control font-size-lg"
                            autocapitalize="none" spellcheck="false"
                            id="btn-inbox-livia" placeholder="Ask to Livia"
                            autofocus="">
                        <button class="btn btn-primary" style="margin-left:10px;"
                            id="btn-ask-livia" onclick="askLivia()"> &nbsp ASK
                            &nbsp </button>
                        <button class="btn btn-primary" style="margin-left:2px"
                            id="btn-ask-livia" onclick="newChat()"> &nbsp NEW
                            &nbsp </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.sidebars.user')

<script>
    var curr_chat_topic_id = -1;
    var curr_chat_topic = "";
    var curr_chat_history = 0;

    window.addEventListener("DOMContentLoaded", function() {
        document.querySelector('footer').style = 'display: none';
    });

    window.currentUser = {!! json_encode(Auth::user()) !!};
    var fullname = window.currentUser.name.split(" ");
    var user_string_avatar = "";
    if (fullname.length==1){
        user_string_avatar = fullname[0].substr(0,2);
    } else if (fullname.length==2) {
        user_string_avatar = (fullname[0].substr(0,1) + fullname[1].substr(0,1));
    } else if (fullname.length==3) {
        user_string_avatar = (fullname[0].substr(0,1) + fullname[2].substr(0,1));
    } else {
        user_string_avatar = fullname[0].substr(0,2);
    }

    function askLivia() {
        let questionTag = document.getElementById("ask");
        let question = questionTag.value;
        if (question == "") {
            return;
        }
        document.getElementById("loadingId").style.display = "block";
        let questionHTML = '<div class="question-me">' +
            '<div class="avatar-width"><div class="avatar avatar-center">' + user_string_avatar + '</div></div><div style="word-break: break-all;">' + question +
            '</div></div>';
        let chatArea = document.getElementById("chat-area")
        chatArea.innerHTML += questionHTML;
        questionTag.value = "";

        if (curr_chat_topic_id == -1 || curr_chat_topic == "") {
            curr_chat_topic = question;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); console.log("ajax started");
            $.ajax({
                type: 'POST',
                url: '/chat/topic/create',
                data: {
                    prompt: question
                },
                success: function(data) {
                    curr_chat_topic_id = data.topic_id;
                    console.log(curr_chat_topic_id);
                    document.getElementById("recent-topic").innerHTML += '<tr>'
                                                                            + '<td class="chat-title-truncate"><p class="chat-title-truncate">' + curr_chat_topic + '</p></td>'
                                                                        + '</tr>';
                }
            });
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: '/chat/ask',
            data: {
                prompt: question
            },
            success: function(data) {
                let answer = data.answer;
                let answerHTML = '<div class="answer-livia" style="white-space: pre-wrap; overflow-y: unset !important" data-gramm="false" contenteditable="false">' +
                    '<div class="avatar-width"><div class="avatar avatar-center"><img src="{{asset('img/livia.png')}}" /></div></div>' + '<div>' + answer + '</div>' +
                    '</div>';

                chatArea.innerHTML += answerHTML;
                document.getElementById("loadingId").style.display = "none";
                curr_chat_history ++;                
            }
        });
    }

    function newChat(){
        document.getElementById("ask").value = "";
        document.getElementById("chat-area").innerHTML = "";
        
        curr_chat_topic_id = 0;
        curr_chat_topic = "";
        curr_chat_history = 0;
        console.log("start new chat");
    }

    function viewTemplate(status) {
        if ( status === 0) {
            document.getElementById("mySidebar").style.right = "0";
            document.getElementById("view-temp-btn").style.display = "none";
        } else {
            document.getElementById("mySidebar").style.right = "-250px";
            document.getElementById("view-temp-btn").style.display = "block";
            document.getElementById("close-temp-btn").style.display = "none";
        }
    }


    window.onclick = function(event) {
        var modal = document.getElementById('mySidebar')
        var buttongroup = document.getElementById('view-temp-btn');
        if (event.target != modal && event.target != buttongroup) {
            document.getElementById("mySidebar").style.right = "-250px";
            document.getElementById("view-temp-btn").style.display = "block";
        }
    }
</script>
