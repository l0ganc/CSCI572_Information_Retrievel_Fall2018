<?php
ini_set('memory_limit', '-1');
include 'SpellCorrector.php';
include 'snippetGenerator.php';

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$rankAlgorithm = isset($_GET['rankAlgorithm']) ? $_GET['rankAlgorithm'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client/Apache/Solr/Service.php');


  // read url from csv file
  $dict = [];
  $mapfile = fopen("URLtoHTML_mercury.csv", "r");
  while (($line = fgets($mapfile)) !== false) {
    $dict[explode(",", $line)[0]] = explode(",", $line)[1];
  }
  fclose($mapfile);

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $solrParameters = array(
      'fl' => 'title,og_url,og_description,id'
    );
    $pagerankParameters = array(
      'sort' => 'pageRankFile desc'
    );

    if ($rankAlgorithm == "solr") {
      $results = $solr->search($query, 0, $limit);
    } else {
      $results = $solr->search($query, 0, $limit, $pagerankParameters);
    }
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}
?>
<html>
  <head>
    <title>CSCI572 HW5 SolrExercise</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

    <script src="https://code.jquery.com/jquery-1.12.4.js" integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>

    <style>
        /*.res{
            width: 1000px;
            background-color: #F3F3F3;
            position: absolute;
            left: 220px;
            top: 200px;

        }*/
        /*table{border-collapse: collapse; width: 95%; border: 1px solid black;}
    table, td, th {
        border: 1px solid #E3E3E3;
    }*/
      .snippet{
        color: red;
      }
      em {
        font-style: italic;
        color: blue;
      }
      </style>
  </head>
  <body>
    <div class="container">
      <div>
        <form  class="jumbotron" accept-charset="utf-8" method="get">
        <div style="text-align: center">
            <h4>Solr query exercise</h4>
        </div>
        <div class="form-group row" style="text-align: center">
          <label class="col-2 col-form-label" for="q">Keyword</label>
          <div class="col-6">
            <input class="form-control" id="q" name="q" type="text" class="form-control" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
          </div>
        </div>

        <div class="form-group row">
          <label for="algo" class="col-2 col-form-label" style="text-align: center">Algorithm</label>
          <div class="col-6">
            <input id="radio1" type="radio" name="rankAlgorithm" <?php if($rankAlgorithm != "pagerank") { echo "checked='checked'"; } ?> value="solr"> Lucene(Solr)
            <input id="radio2" type="radio" name="rankAlgorithm" <?php if($rankAlgorithm == "pagerank") { echo "checked='checked'"; } ?> value="pagerank" style="margin-left: 30px"> PageRank
          </div>
        </div>

        <div class="form-group row">
          <div class="col-2" style="text-align: center">
            <button type="submit" class="btn btn-primary">search</button>
          </div>
        </div>


    </form>
      </div>

    </div>

<?php
// display results
if ($results)
{

  ?>
  <div class="res">
        <?php
        if($query){

          $query = strtolower($query);

          $terms = explode(" ",$query);
          $correct_terms = array();
          $is_spell_error = false;
          for($i=0;$i<sizeof($terms);++$i){
            $term = $terms[$i];
            $correct_term = strtolower(SpellCorrector::correct($term));
            if($term != $correct_term){
              $is_spell_error = true;
            }
            array_push($correct_terms,$correct_term);
          }
          if($is_spell_error){
            $correct_terms = implode(" ",$correct_terms);
            ?>
            <div style="padding: 10px">Did you mean: <a href="index.php?type=solr&q=<?=$correct_terms?>"><?= $correct_terms; ?></a></div>
            <?php
          }
        }
      ?>
    <ol>
  <?php
    //var_dump($results);
    $docs0 = $results->response->docs;
    //var_dump($docs0);
    $docs1 = $results->highlighting;
    //var_dump($docs1);
    // iterate result documents
    for($i = 0;$i < sizeof($docs0); ++$i)
    {
  ?>
        <td>
          <table>
            <tr style="text-align: center; background: grey; color: white;">
              <td colspan="2"><b><?php if ($rankAlgorithm == "solr") {echo "Solr Ranking ";} else {echo "pageRank Ranking ";} echo "#"; echo $i + 1; ?></b></td>
            </tr>

        <?php

              $doc = $docs0[$i];
              $real_id = substr($doc->id,strripos($doc->id,"/")+1);
              $url = $dict[$real_id];
              ?>
              <tr>
                  <th>id</th>
                  <td><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>
              </tr>
              <tr>
                  <th>title</th>
                  <td><a href='<?php echo $url; ?>' target="_blank"><?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8'); ?></a></td>
              </tr>

              <tr>
                <th>url</th>
                <td style="color:green; font-size:14px" >
                  <?php echo $url; ?>
                </td>
              </tr>
              <tr>
                  <th>description</th>
                  <td style="color:gray; font-size:14px" ><?php echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8'); ?></td>
              </tr>
              <tr>
                  <th>snippet</th>
                  <td><?php
                    $snippet = generate_snippet($real_id, end($terms));
                    $snippet = decorate_snippet($terms, $snippet);
                    echo $snippet;
                  ?></td>
              </tr>
              <?php

              ?>

          </table>

  <?php
    }
  ?>
      </ol>
      </div>


<?php
}
?>
  </body>
  <script>
        function clearForm() {
            //$("#q").val() = "";
            //document.getElementById("q").reset();
            //<a href="query.php?type=solr&q=<?=$correct_terms?>">
            window.location.href = "index.php";
        }
        $(function() {
            var URL_PREFIX = "http://localhost:8983/solr/myexample/suggest?q=";
            var URL_SUFFIX = "&wt=json";
            $("#q").autocomplete({
                source : function(request, response) {
                    var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                    var URL = URL_PREFIX + lastword + URL_SUFFIX;
                    $.ajax({
                        url : URL,
                        success : function(data) {
                            var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                            var suggestions = data.suggest.suggest[lastword].suggestions;
                            suggestions = $.map(suggestions, function (value, index) {
                                var prefix = "";
                                var query = $("#q").val();
                                var queries = query.split(" ");
                                if (queries.length > 1) {
                                    var lastIndex = query.lastIndexOf(" ");
                                    prefix = query.substring(0, lastIndex + 1).toLowerCase();
                                }
                                if (prefix == "" && isStopWord(value.term)) {
                                    return null;
                                }
                                if (!/^[0-9a-zA-Z]+$/.test(value.term)) {
                                    return null;
                                }
                                return prefix + value.term;
                            });
                            response(suggestions.slice(0, 5));
                        },
                        dataType : 'jsonp',
                        jsonp : 'json.wrf'
                    });
                },
                minLength : 1
            });
        });
        function isStopWord(word)
        {
            var regex = new RegExp("\\b"+word+"\\b","i");
            return stopWords.search(regex) < 0 ? false : true;
        }
        var stopWords = "a,able,about,above,abst,accordance,according,accordingly,across,act,actually,added,adj,\
        affected,affecting,affects,after,afterwards,again,against,ah,all,almost,alone,along,already,also,although,\
        always,am,among,amongst,an,and,announce,another,any,anybody,anyhow,anymore,anyone,anything,anyway,anyways,\
        anywhere,apparently,approximately,are,aren,arent,arise,around,as,aside,ask,asking,at,auth,available,away,awfully,\
        b,back,be,became,because,become,becomes,becoming,been,before,beforehand,begin,beginning,beginnings,begins,behind,\
        being,believe,below,beside,besides,between,beyond,biol,both,brief,briefly,but,by,c,ca,came,can,cannot,can't,cause,causes,\
        certain,certainly,co,com,come,comes,contain,containing,contains,could,couldnt,d,date,did,didn't,different,do,does,doesn't,\
        doing,done,don't,down,downwards,due,during,e,each,ed,edu,effect,eg,eight,eighty,either,else,elsewhere,end,ending,enough,\
        especially,et,et-al,etc,even,ever,every,everybody,everyone,everything,everywhere,ex,except,f,far,few,ff,fifth,first,five,fix,\
        followed,following,follows,for,former,formerly,forth,found,four,from,further,furthermore,g,gave,get,gets,getting,give,given,gives,\
        giving,go,goes,gone,got,gotten,h,had,happens,hardly,has,hasn't,have,haven't,having,he,hed,hence,her,here,hereafter,hereby,herein,\
        heres,hereupon,hers,herself,hes,hi,hid,him,himself,his,hither,home,how,howbeit,however,hundred,i,id,ie,if,i'll,im,immediate,\
        immediately,importance,important,in,inc,indeed,index,information,instead,into,invention,inward,is,isn't,it,itd,it'll,its,itself,\
        i've,j,just,k,keep,keeps,kept,kg,km,know,known,knows,l,largely,last,lately,later,latter,latterly,least,less,lest,let,lets,like,\
        liked,likely,line,little,'ll,look,looking,looks,ltd,m,made,mainly,make,makes,many,may,maybe,me,mean,means,meantime,meanwhile,\
        merely,mg,might,million,miss,ml,more,moreover,most,mostly,mr,mrs,much,mug,must,my,myself,n,na,name,namely,nay,nd,near,nearly,\
        necessarily,necessary,need,needs,neither,never,nevertheless,new,next,nine,ninety,no,nobody,non,none,nonetheless,noone,nor,\
        normally,nos,not,noted,nothing,now,nowhere,o,obtain,obtained,obviously,of,off,often,oh,ok,okay,old,omitted,on,once,one,ones,\
        only,onto,or,ord,other,others,otherwise,ought,our,ours,ourselves,out,outside,over,overall,owing,own,p,page,pages,part,\
        particular,particularly,past,per,perhaps,placed,please,plus,poorly,possible,possibly,potentially,pp,predominantly,present,\
        previously,primarily,probably,promptly,proud,provides,put,q,que,quickly,quite,qv,r,ran,rather,rd,re,readily,really,recent,\
        recently,ref,refs,regarding,regardless,regards,related,relatively,research,respectively,resulted,resulting,results,right,run,s,\
        said,same,saw,say,saying,says,sec,section,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sent,seven,several,shall,she,shed,\
        she'll,shes,should,shouldn't,show,showed,shown,showns,shows,significant,significantly,similar,similarly,since,six,slightly,so,\
        some,somebody,somehow,someone,somethan,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specifically,specified,specify,\
        specifying,still,stop,strongly,sub,substantially,successfully,such,sufficiently,suggest,sup,sure,t,take,taken,taking,tell,tends,\
        th,than,thank,thanks,thanx,that,that'll,thats,that've,the,their,theirs,them,themselves,then,thence,there,thereafter,thereby,\
        thered,therefore,therein,there'll,thereof,therere,theres,thereto,thereupon,there've,these,they,theyd,they'll,theyre,they've,\
        think,this,those,thou,though,thoughh,thousand,throug,through,throughout,thru,thus,til,tip,to,together,too,took,toward,towards,\
        tried,tries,truly,try,trying,ts,twice,two,u,un,under,unfortunately,unless,unlike,unlikely,until,unto,up,upon,ups,us,use,used,\
        useful,usefully,usefulness,uses,using,usually,v,value,various,'ve,very,via,viz,vol,vols,vs,w,want,wants,was,wasn't,way,we,wed,\
        welcome,we'll,went,were,weren't,we've,what,whatever,what'll,whats,when,whence,whenever,where,whereafter,whereas,whereby,wherein,\
        wheres,whereupon,wherever,whether,which,while,whim,whither,who,whod,whoever,whole,who'll,whom,whomever,whos,whose,why,widely,\
        willing,wish,with,within,without,won't,words,world,would,wouldn't,www,x,y,yes,yet,you,youd,you'll,your,youre,yours,yourself,\
        yourselves,you've,z,zero";
    </script>
</html>
