<?php
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$rankAlgorithm = isset($_GET['rankAlgorithm']) ? $_GET['rankAlgorithm'] : false;
$results = false;
$solrParameters = array(
  'fl' => 'title,og_url,og_description,id'
);
$pagerankParameters = array(
  'fl' => 'title,og_url,og_description,id',
  'sort' => 'pageRankFile desc'
);
if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client/Apache/Solr/Service.php');
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
    if ($rankAlgorithm == "solr") {
      $results = $solr->search($query, 0, $limit, $solrParameters);
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
    <title>CSCI572 HW4 SolrExercise</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
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
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
  // $inputFile = file("/Users/dreamysx/Documents/USC/572/hw/hw4/FOX_News/UrlToHtml_foxnews.csv");
 
  // foreach ($inputFile as $line) {
  //   $file = str_getcsv($line);
  //   $fileUrlMap[$file[0]] = trim($file[1]);
  // }
?>
    <div style="text-align: center">Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
    $title = $doc->title;
    $id = $doc->id;
 
    $key = str_replace("/Users/logan/Downloads/IR--2018/mercurynews/mercurynews/","",$id); 
 
    $description = $doc->og_description;
    $url = $doc->og_url;
    
    ?>
    <li><b><a href="<?php echo $url ?>" target="_blank"><?php 
    if (isset($doc->title)) {
        echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');
      } else {
        echo "NA";
      } ?> </a></b><br>
      <i><a href="<?php echo $url ?>" target="_blank"><?php echo $url ?></a></i><br>
      <?php echo $key ?> <br>
      <?php 
      if (isset($doc->og_description)) {
        echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8');
      } else {
        echo "NA";
      }
      ?>
    </li>
    <br>

<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>