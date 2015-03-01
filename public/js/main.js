function toggleHightlightItemAction() {
    if($(this).parent().parent().hasClass('item_hover')) {
        $(this).parent().parent().removeClass('item_hover');
    } else {
        $(this).parent().parent().addClass('item_hover');
    }
}

function toggleItemTap() {
    var element = this;
    var tapped = $(element).parent().hasClass('tapped');
    getItemIdToTap(element, function(item_id) {
        tapItemDB(item_id, tapped, function(result) {
            if(result) {
                tapItemHTML(element, tapped);
            } else {
                alert('failure');
                location.reload();
            }
        });
    });
}

function tapItemHTML(element, tapped) {
    if(tapped) {
        $(element).parent().removeClass('tapped');
        $(element).parent().addClass('untapped');
    } else {
        $(element).parent().removeClass('untapped');
        $(element).parent().addClass('tapped');
    }
}

function tapItemDB(item_id, tapped, callback) {
    $.ajax({
        type: "POST",
        url: "/member/tapItem",
        data: {id: item_id, tap: tapped}
    }).done(function(result) {
        callback(result);
    });
}

function getItemIdToTap(element, callback) {
    var item_id = element.getAttribute('id').substring(1);
    callback(item_id);
}

function toggleMenu() {
    var el = document.getElementById('nav_list');
    var nav = document.getElementById('hamburger_holder');
    if(window.getComputedStyle(el, null).getPropertyValue('display') === 'none') {
        el.style.display='block';
        document.addEventListener('click', closeMenuHandler);
    } else {
        el.style.display='none';
    }
}

function closeMenuHandler(e) {
    var el = document.getElementById('nav_list');
    var clicked = e.target.id;
    if(clicked !== 'hamburger') {
        el.style.display='none';
        document.removeEventListener('click', closeMenuHandler);
    }
}

function logout() {
    var logout = confirm('Are you sure you want to logout?');
    if(logout) {
        window.location = '/logout';
    }

    return false;
}

function initDeleteInviter() {
    getInviterIdsToDelete(this, function(user_id, list_id) {
        deleteInviter(user_id, list_id, function() {
            location.reload();
        });
    });
}

function getInviterIdsToDelete(element, callback) {
    var list_id = element.parentNode.parentNode.childNodes[1].childNodes[3].getAttribute('id').substring(1);
    var user_id = element.parentNode.parentNode.childNodes[1].childNodes[1].getAttribute('id').substring(1);
    callback(encodeURIComponent(user_id), encodeURIComponent(list_id));
}

function deleteInviter(user_id, list_id, callback) {
    var result;

    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            result = xmlhttp.responseText;
            callback();
        }
    }

    var param1 = "user_id=".concat(user_id);
    var param2 = "&list_id=".concat(list_id);
    var params = param1.concat(param2);
    xmlhttp.open("POST", "deleteInviter", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);
}

function initDeleteMembership() {
    getMembershipListIdToDelete(this, function(list_id) {
        deleteMembership(list_id, function() {
            location.reload();
        });
    });
}

function getMembershipListIdToDelete(element, callback) {
    var list_id = element.parentNode.parentNode.childNodes[1].childNodes[1].getAttribute('id').substring(1);
    callback(encodeURIComponent(list_id));
}

function deleteMembership(list_id, callback) {
    var result;

    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            result = xmlhttp.responseText;
            callback();
        }
    }

    var param = "list_id=".concat(list_id);
    xmlhttp.open("POST", "deleteMembership", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(param);
}

function initDeleteInvited() {
    getInvitedListToDelete(this, function(list_id) {
        deleteInvited(list_id, function() {
            location.reload();
        });
    });
}

function getInvitedListToDelete(element, callback) {
    var list_id = element.parentNode.parentNode.childNodes[1].childNodes[1].getAttribute('id').substring(1);
    callback(encodeURIComponent(list_id));
}

function deleteInvited(list_id, callback) {
    var result;

    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            result = xmlhttp.responseText;
            callback();
        }
    }

    var param = "list_id=".concat(list_id);
    xmlhttp.open("POST", "deleteInvited", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(param);
}

function initDeleteMember() {
    getMemberIdToDelete(this, function(member_id) {
        getDeleteMemberListId(function(list_id) {
            deleteMember(list_id, member_id, function() {
                location.reload();
            });
        });
    });
}

function getDeleteMemberListId(callback) {
    var list_id = document.getElementsByName('list_id')[0].value;
    callback(encodeURIComponent(list_id));
}

function getMemberIdToDelete(element, callback) {
    var member_id = element.parentNode.parentNode.childNodes[1].childNodes[0].getAttribute('id').substring(1);
    callback(encodeURIComponent(member_id));
}

function deleteMember(list_id, member_id, callback) {
    var result;

    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            result = xmlhttp.responseText;
            callback();
        }
    }

    var param1 = "list_id=".concat(list_id);
    var param2 = "&member_id=".concat(member_id);
    var params = param1.concat(param2);
    xmlhttp.open("POST", "../editList/deleteMember", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);
}

function initDeleteList() {
    if(confirm('Are you sure you want to delete this list?')) {
        getListIdToDelete(this, function(list_id) {
            deleteList(list_id, function() {
                location.reload();
            });
        });
    }
}

function getListIdToDelete(element, callback) {
    var list_id = element.parentNode.parentNode.childNodes[3].childNodes[1].childNodes[1].value;
    callback(encodeURIComponent(list_id));
}

function deleteList(list_id, callback) {
    var result;

    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            result = xmlhttp.responseText;
            callback();
        }
    }

    var param = "list_id=".concat(list_id);
    xmlhttp.open("POST", "../edit/deleteList", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(param);
}

function initDeleteItem() {
    getItemIdToDelete(this, function(item_id) {
        getListId(function(list_id) {
            deleteItem(list_id, item_id, function() {
                location.reload();
            });
        });
    });
}

function getListId(callback) {
    var list_id = document.getElementById('working_list').value;
    callback(encodeURIComponent(list_id));
}

function getItemIdToDelete(element, callback) {
    var item_id = element.parentNode.parentNode.childNodes[1].getAttribute('id').substring(1);
    callback(encodeURIComponent(item_id));
}

function deleteItem(list_id, item_id, callback) {
    var result;

    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            result = xmlhttp.responseText;
            callback();
        }
    }

    var param1 = "list_id=".concat(list_id);
    var param2 = "&item_id=".concat(item_id);
    var params = param1.concat(param2);
    xmlhttp.open("POST", "/member/deleteItem", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);
}

function selectList() {
    $(this).on('change', function() {
        var value = $(this).val();
        var location = '/member/list/'.concat(value).concat('/');
        window.location.href = location;
    });
}

function addListeners(elements, funcName) {
    for(var i = 0; i < elements.length; i++) {
        if(elements[i] !== null) {
            elements[i].addEventListener('click', funcName, false);
        }
    }
}

function init() {
    var elements = document.getElementsByClassName('item_action');
    for(var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('mouseover', toggleHightlightItemAction);
    }
    var elements = document.getElementsByClassName('item_action');
    for(var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('mouseout', toggleHightlightItemAction);
    }

    var element = document.getElementById('select_list');
    if(element !== null) {
        element.addEventListener('click', selectList);
    }

    var elements = document.getElementsByClassName('item_title');
    addListeners(elements, toggleItemTap);

    var element = document.getElementById('hamburger');
    element.addEventListener('click', toggleMenu);

    var elements = new Array();
    elements = document.getElementsByClassName('delete_inviter');
    addListeners(elements, initDeleteInviter);

    var elements = new Array();
    elements = document.getElementsByClassName('delete_membership');
    addListeners(elements, initDeleteMembership);

    var elements = new Array();
    elements = document.getElementsByClassName('delete_invited');
    addListeners(elements, initDeleteInvited);

    var elements = new Array();
    elements = document.getElementsByClassName('delete_member');
    addListeners(elements, initDeleteMember);

    var elements = new Array();
    elements = document.getElementsByClassName('delete_item');
    addListeners(elements, initDeleteItem);

    var elements = new Array();
    elements = document.getElementsByClassName('delete_list');
    addListeners(elements, initDeleteList);
}

window.onload = function() {
    init();
}