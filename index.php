<html>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js"></script>
<script type="text/javascript" src="http://timecard.mindia.jp/js/SortedList.js"></script>
<script type="text/javascript" src="http://timecard.mindia.jp/js/jsrel.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>mindia</title>
<body>
<div id="words">now loading...</div>
<form id="add_word">
<input type="text" id="input" />
<input type="submit" value="add" />
</form>

<script>
var w;


var db = JSRel.use("dbname", 
  {
    schema: {
      users: { name : "", server_id: 0, $uniques: "name"},
      words: { title : "", body: "", server_id: 0, $uniques: "title"},
      word_rels: { parent_id : 0, child_id: 0, user_id:0, server_id: 0}
    }
  }
);

function c(val){
  console.log(val);
}

var Word = Backbone.Model.extend({
  create: function(title){
    this.set("title", title);
    this.save();
    return this;
  },
  save: function(){
    var title = this.get("title");
    var word = db.one('words', {title: title});
    if(!word){
      var word = db.ins('words', {title: title});
    }else{
      db.upd("words", word);
    }
    this.set("id", word.id);
  },
  addParent: function(title){
    var parent = new Word();
    parent.set("title", title);
    parent.save();
    db.ins('word_rels', {parent_id: parent.get("id"), child_id: this.get("id")});
  },
  findAll: function(title){
    return new Words(db.find('words', {title: {gt: title}}, {order:"title", limit:30}))
  }
});

var Words = Backbone.Collection.extend({
  model: Word
});

var WordView = Backbone.View.extend({
  tagName: "div",
  render: function(){
    var title = this.model.get("title");
    var href = $('<a></a>').text(title).attr("href", "/"+title);
    this.$el.html(href);
    return this;
  }
});

var WordsView = Backbone.View.extend({
  el: "#words",
  initialize: function(){
    this.collection.on("add" ,this.addWord, this);
  },
  addWord: function(word){
    var wordView = new WordView({model: word});
    this.$el.append(wordView.render().el);
    $("#input").val("").focus();
  },
  render: function(){
    this.$el.html("");
    this.collection.each(function(word){
      var wordView = new WordView({model: word});
      this.$el.append(wordView.render().el);
    }, this);
    return this;
  }
});

var addWordView = Backbone.View.extend({
  el: "#add_word",
  events: {
    "submit": "submit"
  },
  submit: function(e){
    e.preventDefault();
    title = $(e.target).find("#input").val();
    var word = new Word();
    if (word.set({title: title}, {validate: true})) {
      word.save();
      this.collection.add(word);
    }
  }
});

var Router = Backbone.Router.extend({
  routes: {
    "": "words",
    ":title": "words"
  },
  words: function(title){
    if(!title){
      title = "Internet";
    }
    c(title);
    words = new Word().findAll(title);
    new WordsView({
      collection: words
    }).render();

    new addWordView({
      collection: words
    }).render();

    $("#input").focus();
  }
});

var router = new Router();

w = new Word().create("Shinran");
w.addParent("Shigesato_Itoi");

<?php
/*
var mindia_words = <?php echo file_get_contents("http://mindia.jp/?module=book_keyword_json&book=nishiko"); ?>;
for(var i=0; i < mindia_words.length; i++){
  new Word().create(mindia_words[i]);
}
*/
?>
db.save();
Backbone.history.start({ pushState: true});

</script>
