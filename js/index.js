var endPoint = './action.php'
var total = 0
var progress = 0

/**
 * ぶっこ抜くボタン押下時 
 */
$('#submit').click(function() {
    // Debug用。phpとgoを切り替える
    var serverLang = getParam('lang') 
    if(serverLang == 'go') {
        endPoint = 'https://nameless-sands-66548.herokuapp.com/instagram'
    }

    var urls = $('#urls').val().split('\n').filter(function(v) { return v.match(/https:\/\/www\.instagram\.com/)})
    total = urls.length
    // exec(urls)
    urls.forEach(function(url) { exec([url]) })
})

function exec(urls) {
    $('#result-view-title').text('取得中...')
    $('#result-view-progress').text(`${progress}/${total}`)
    var data ={
        'URLs' : urls,
    }
    $.ajax({
        url : endPoint,
        type:'POST',
        contentType: "application/json",
        dataType: "json",
        xhrFields: {
            withCredentials: true
        },
        data:JSON.stringify(data)
    })
    .done((data) => {
        data.forEach(function(value) {
            addNewCard(value.ImageURL, value.PostText, value.Username, value.OrgURL, value.Err)
        })
    })
    .fail((data) => {
        addNewCard('./../assets/no-image.png', '', data.responseText, '')
    })
    .always((data) => {
        $('#result-view-title').text('結果')
        progress++
        $('#result-view-progress').text(`${progress}/${total}`)
    });
}

/**
 * Result-Viewにカードを追加
 */
function addNewCard(imageUrl, postText, username, orgUrl, error) {
    var newCard = $('#result-card').clone()
    newCard.removeAttr('hidden')
    newCard.find('.post-image').attr('src', imageUrl)
    newCard.find('.username').text(username)
    newCard.find('.org-url').attr('href', orgUrl)
    newCard.find('.post-text').text(postText)
    if(error != '') {
        newCard.find('.username').text('取得できませんでした')
    }
    newCard.appendTo('#result-view')
}

/**
 * Chatwork送信有無スイッチ切替時
 */
$('#allow-send-chatwork').change(function() {
    console.log($('#allow-send-chatwork').prop('checked'))
})


/**
 * Get the URL parameter value
 *
 * @param  name {string} パラメータのキー文字列
 * @return  url {url} 対象のURL文字列（任意）
 */
function getParam(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
