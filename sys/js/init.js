$(document).ready(function () {

    $('.tree-hide-all').live('click', collapse);
    $('.tree-show-all').live('click', expand);
    $('.rem-leaf').live('click', removeNode);
    $('.del-leaf').live('click', deleteNode);
    $('.move-up').live('click', moveUp);
    $('.move-down').live('click', moveDown);
    $('.add-leaf').live('click', addLeaf);
    $('.edit-leaf').live('click', editLeaf);
    $('.submit').live('click', validateForm);
    $('.edit_submit').live('click', validateEdit);

    function parseId(id) {

        id = /\d{1,}$/.exec(id);

        return id;

    }

    function collapse() {

        var clickedId = $(this).parentsUntil("li").parent().attr("id");
        var clickedClass = $(this).attr("class");

        $("li#" + clickedId + " > ul").slideUp("slow", changeIconColl(clickedId, clickedClass));

    }

    function expand() {

        var clickedId = $(this).parentsUntil("li").parent().attr("id");
        var clickedClass = $(this).attr("class");

        if ( $("li#" + clickedId + " > ul").length > 0 ) {

            $("li#" + clickedId + " > ul").slideDown("slow", changeIconExp(clickedId, clickedClass));

        }else {

            id = parseId(clickedId);

            var dataString =  'id='+id;

            $.ajax({
                type: "GET",
                url: "sys/getNode.php",
                data: dataString,
                success: function(data) {

                    $("li#" + clickedId + " div").after(data).slideDown("slow", changeIconExp(clickedId, clickedClass));

                }
            });
        }
    }

    function changeIconColl(clickedId, clickedClass) {

        if (clickedClass = 'tree-open') {

            $("li#" + clickedId).attr('class', 'tree-closed');
            $("li#" + clickedId + " .tree-hide-all").first().attr('title', 'expand');
            $("li#" + clickedId + " .tree-hide-all").first().attr('class', 'tree-show-all');

        }

    }

    function changeIconExp(clickedId, clickedClass) {

        if (clickedClass = 'tree-closed') {

            $("li#" + clickedId).attr('class', 'tree-open');
            $("li#" + clickedId + " .tree-show-all").first().attr('title', 'collapse');
            $("li#" + clickedId + " .tree-show-all").first().attr('class', 'tree-hide-all');

        }

    }

    function moveUp() {

        var clickedId = $(this).parentsUntil('li').parent().attr("id");
        var clickedClass = $(this).attr("class");

        clickedId = parseId(clickedId);

        var dataString =  'id='+clickedId+'&func=moveUp';

        $.ajax({
            type: "GET",
            url: "sys/manipulateNode.php",
            data: dataString,
            success: function(data) {
                reNewTree(data);
            }
        });

    }

    function moveDown() {

        var clickedId = $(this).parentsUntil('li').parent().attr("id");
        var clickedClass = $(this).attr("class");

        clickedId = parseId(clickedId);

        var dataString =  'id='+clickedId+'&func=moveDown';

        $.ajax({
            type: "GET",
            url: "sys/manipulateNode.php",
            data: dataString,
            success: function(data) {
                reNewTree(data);
            }
        });

    }


    function removeNode() {

        var clickedId = $(this).parentsUntil('li').parent().attr("id");
        var clickedClass = $(this).attr("class");

        clickedId = parseId(clickedId);

        if (clickedId != null) {

            var answ = confirm("Are you shure want to remove comment with id = "+clickedId+"?");

            if (answ == true) {

                var dataString =  'id='+clickedId+'&func=remove';

                $.ajax({
                    type: "GET",
                    url: "sys/manipulateNode.php",
                    data: dataString,
                    success: function(data) {
                        reNewTree(data);
                    }
                });

            }else {

            }
        }

    }

    function deleteNode() {

        var clickedId = $(this).parentsUntil('li').parent().attr("id");

        clickedId = parseId(clickedId);

        if (clickedId != null) {

            var answ = confirm("Are you shure want to delete node with id = "+clickedId+" and all comment ?");

            if (answ == true) {

                var dataString =  'id='+clickedId+'&func=delete';

                $.ajax({
                    type: "GET",
                    url: "sys/manipulateNode.php",
                    data: dataString,
                    success: function(data) {
                        reNewTree(data);
                    }
                });

            }

        }

    }

    function reNewTree(data) {

        if (data != null) {

            $('div#tree').empty();
            $('#load').remove();

            $('div#tree').append('<div id="load">LOADING...</div>');
            $('#load').fadeIn('normal');

            $('div#tree').append(data).slideDown("slow", emptyDiv());

        }

    }

    function emptyDiv() {
        $('div#load').remove();
    }

    function addLeaf() {

        var clickedId = $(this).parentsUntil('li').parent().attr("id");
        var addField = $("#addComment").css("display");
        var editField = $("#editComment").css("display");

        clickedId = parseId(clickedId);

            $("p.hidden").empty();
            $("#addComment").css("display", "block");
            $("#addComment p.hidden").append('<input name="id" id="id" type="hidden" value="'+clickedId+'" />');
            $("#editComment").css("display", "none");

    }

    function editLeaf () {

        var clickedId = $(this).parentsUntil('li').parent().attr("id");
        var editField = $("#editComment").css("display");

        clickedId = parseId(clickedId);

            var dataString = 'id='+ clickedId;
//            alert(dataString);
            $.ajax({
                type: "GET",
                url: "sys/editNode.php",
                data: dataString,
                success: function(data) {
                    $('#editComment').empty();
                    $('#editComment').append(data)
                    $("#editComment").css("display", "block");
                    $("#addComment").css("display", "none");
                }
            });


    }

    function validateForm() {

        var id = $("input#id").val();

        id = parseId(id);

        if (id == "") {
            alert("Unknown id");
            return false;
        }

        var title = $("input#title").val();
        if (title == "") {
            alert("Enter title please");
            $("input#title").focus();
            return false;
        }

        var text = $("textarea#text").val();
        if (text == "") {
            alert("Enter text please");
            $("input#text").focus();
            return false;
        }

            sendForm(id, title, text);
            return false;

    }

    function validateEdit() {

        var idEd = $("input#id_edit").val();

        idEd = parseId(idEd);

        if (idEd == "") {
            alert("Unknown id");
            return false;
        }

        var titleEd = $("input#title_edit").val();
        if (titleEd == "") {
            alert("Enter title please");
            $("input#title").focus();
            return false;
        }

        var textEd = $("textarea#text_edit").val();
        if (textEd == "") {
            alert("Enter text please");
            $("input#text").focus();
            return false;
        }

            updateForm(idEd, titleEd, textEd);
            return false;

    }

    function sendForm(id, title, text) {

        var dataString = 'id='+ id + '&title=' + title + '&text=' + text + '&func=addNode';
//        alert (dataString);return false;
        $.ajax({
            type: "GET",
            url: "sys/manipulateNode.php",
            data: dataString,
            success: function(data) {
                reNewTree(data);
                clearForm();
            }
        });
        return false;

    }

    function updateForm(id, title, text) {

        var dataString = 'id='+ id + '&title=' + title + '&text=' + text + '&func=updNode';
//        alert (dataString);return false;
        $.ajax({
            type: "GET",
            url: "sys/manipulateNode.php",
            data: dataString,
            success: function(data) {
                reNewTree(data);
                clearForm();
            }
        });
        return false;

    }

    function clearForm() {

        $("#id").val("");
        $("#title").val("");
        $("#text").val("");
        $("#addComment").css("display", "none");
        $("#editComment").css("display", "none");

    }

});
