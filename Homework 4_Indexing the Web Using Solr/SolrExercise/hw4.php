<?php
	// make sure browsers see this page as utf-8 encoded HTML
	header('Content-Type: text/html; charset=utf-8');
	$limit = 10;
	$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
	$results = false;
	$pagerank_results = false;

	if ($query) {
		// The Apache Solr Client library should be on the include path // which is usually most easily accomplished by placing in the
		// same directory as this script ( . or current directory is a default // php include path entry in the php.ini) require_once('Apache/Solr/Service.php');
		// create a new solr service instance - host, port, and corename
		// path (all defaults in this example)
		// require_once('/Users/logan/Desktop/Fall_2018/CSCI572/HW/Homework\ 4_Indexing\ the\ Web\ Using\ Solr/SolrExercise/solr-php-client/Apache/Solr/Service.php');
		require_once('/solr-php-client/Apache/Solr/Service.php');
		$solr = new Apache_Solr_Service('localhost', 8983, '/Users/logan/Downloads/solr-7.5.0/server/solr/myexample/');
		// if magic quotes is enabled then stripslashes will be needed
		if (get_magic_quotes_gpc() == 1) {
			$query = stripslashes($query);
		}
		// in production code you'll always want to use a try /catch for any
		// possible exceptions emitted by searching (i.e. connection
		// problems or a query parsing error)
		try {
			$results = $solr->search($query, 0, $limit);

			$additionalParameters = array(
				'sort' => 'pageRankFile desc'
			);

			$pagerank_results = $solr -> search($query, 0, $limit, $additionalParameters);

		}	catch (Exception $e) {
			// in production you'd probably log or email this error to an admin
 			// and then show a special message to the user but for this example
 			// we're going to show the full exception
 			die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
		}
	}

 ?>

 <html>
	<head>
			<title>PHP Solr Client Example</title>
	</head>
	<body>
		<form accept-charset="utf-8" method="get">
 			<label for="q">Search:</label>
 			<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
 			<input type="submit"/>
 		</form>

<?php
		// display results
			if ($results) {
				$total = (int) $results->response->numFound;
				$start = min(1, $total);
				$end = min($limit, $total);
		 ?>
		 	<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
			<ol>

		<?php
				$docs0 = $results -> response -> docs;
				$docs1 = $pagerank_results -> response -> docs;

					// iterate result documents
					for ($i = 0; $i < 10; ++$i) {
		?>
						<li>
							<table>
								<tr>
									<td width="50%" valign="top">
										<table style="border: 1px solid black; text-align: left">
											<tr style="text-align: center; background: gray; color: white;">
												<td colspan="2"><b>Internal Ranking</b></td>
											</tr>
					<?php
						// iterate document fields / values
						if ($i < sizeof($docs0)) {
							$doc = $docs[$si];
							?>
							<tr>
								<th>id</th>
								<td><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>
							</tr>
							<tr>
								<th>description</th>
								<td><?php echo htmlspecialchars($doc->description, ENT_NOQUOTES, 'utf-8'); ?></td>
							</tr>
							<?php
						}
							 ?>
						 </table>
					</td>
					<td width="50%" valign="top">
	          <table style="border: 1px solid black; text-align: left">
	            <tr style="text-align: center; background: grey; color: white;">
	              <td colspan="2"><b>PageRank Ranking</b></td>
	            </tr>
				 <?php
				 // iterate document fields / values
				 if($i < sizeof($docs1))
	         {
	             $doc = $docs1[$i];
	             ?>
	             <tr>
	                 <th>id</th>
	                 <td><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>
	             </tr>
	             <tr>
	                 <th>description</th>
	                 <td><?php echo htmlspecialchars($doc->description, ENT_NOQUOTES, 'utf-8'); ?></td>
	             </tr>
	           <?php
	         }
				  ?>
					</table>
				</td>
			</tr>
		</table>
	</li>
<?php
}
 ?>

</ol>

<?php
}
 ?>
	</body>
 </html>
