<?php
namespace Idk;

class ExceptionHandler {
	
		
	public static function handle($e) {
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html>
			<head>
				<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
				<title>I Didnt Know</title>
			</head>
			<body>
			
				<div id="header">
					<div id="header_inner" class="fixed">
						<div id="logo">
							<h1><span>I didnt know</span></h1>
							<h2>a PHP MVC framework built for speed of development</h2>
						</div>
					</div>
				</div>
			
				
				<div id="main">
					<div id="main_inner" class="fixed">
						<div id="primaryContent_columnless">
							<h3>Oops, an error has occoured!</h3>
							<table style="width: 100%; margin-bottom: 10px;">
								<tr>
									<td style="width: 150px;"><strong>Message:</strong></td>
									<td><?php echo $e->getMessage(); ?></td>
								</tr>
								<tr>
									<td style="width: 150px;"><strong>Occoured at:</strong></td>
									<td><?php echo 'Line: '.$e->getLine() . ' at ' .$e->getFile(); ?></td>
								</tr>
							</table>
							<h3>The stack trace</h3>
							<?php foreach($e->getTrace() as $trace) {?>
								<span><?php echo 'Line: '.$trace['line'] . ' at ' .$trace['file']; ?></span>
								<table style="width: 100%; margin-bottom: 10px;">
										<tr>
									<td style="width: 150px;"><strong>Class:</strong></td>
									<td><?php echo $trace['class']; ?></td>
								</tr>
								<tr>
									<td style="width: 150px;"><strong>Function:</strong></td>
									<td><?php echo $trace['function']; ?></td>
								</tr>
								<tr>
									<td style="width: 150px;"><strong>Arguments:</strong></td>
									<td><?php var_dump($trace['args']); ?></td>
								</tr>
							</table>
							<?php 
							}
							?>
						</div>
						<br class="clear" />
					</div>
				</div>
			</body>
		</html>
		<?php 
		die();
	}
}