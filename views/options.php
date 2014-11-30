<div class="wrap">    
    <h2><?php echo $title;?></h2>

    <table class="wp-list-table widefat fixed">
    	<thead>
    		<tr>
    			<th>Title</th>
    			<th>URL</th>
    			<th>Action</th>
    		</tr>
    	</thead>
    	<tbody>

    		<?php foreach ($movies as $movie):?>
    		<tr>
    			<td><?php echo $movie->title;?></td>
    			<td><a href="<?php echo $movie->url;?>"><?php echo $movie->url;?></a></td>
    			<td><a href="">X</a></td>
    		</tr>
    		<? endforeach;?>
    	</tbody>
    </table>
</div>

