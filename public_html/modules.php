<?php
$config = parse_ini_file("../config.ini");
$conn = mysqli_connect($config['host'], $config['username'], $config['password'], $config['dbname'], $config['port']);

// Attempt select query execution
$sql = "SELECT * FROM nfcore_modules ORDER BY LOWER(name)";
$modules = [];
if ($result = mysqli_query($conn, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $row['keywords'] = explode(';', $row['keywords']);
            $row['authors'] = explode(';', $row['authors']);
            $row['tools'] = json_decode($row['tools'], true);
            $row['input'] = json_decode($row['input'], true);
            $row['output'] = json_decode($row['output'], true);
            $modules[] = $row;
        }
        // Free result set
        mysqli_free_result($result);
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
}
$sql = "SELECT keywords FROM nfcore_modules ";
$keywords = [];
if ($result = mysqli_query($conn, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $keywords[] = explode(';', $row['keywords']);
        }
        // Free result set
        mysqli_free_result($result);
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
}
$keywords_tmp = [];
array_walk_recursive($keywords, function ($v) use (&$keywords_tmp) {
    $keywords_tmp[] = $v;
});
$keywords = $keywords_tmp;

// Close connection
mysqli_close($conn);

$title = 'Modules';
$subtitle = 'Browse the <strong>' . count($modules) . '</strong> modules that are currently available as part of nf-core.';
include('../includes/header.php');

?>

<h1>Available Modules</h1>

<div class="btn-toolbar mb-4 modules-toolbar" role="toolbar">
    <div class="module-filters input-group input-group-sm ms-2 mt-2">
        <input type="search" class="form-control" placeholder="Search keywords" value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>">
    </div>
</div>
<div class="row flex-wrap-reverse flex-lg-wrap me-lg-5">
    <div class="col-12 col-lg-3 pe-2">
        <div class="facet-bar">

            <?php $keywords_value = array_count_values($keywords);
            arsort($keywords_value);
            ?>
            <ul class="list-unstyled">
                <?php foreach ($keywords_value as $idx => $keyword) : ?>
                    <li class="facet-item">
                        <span class="facet-name"><?php echo trim($idx); ?></span>
                        <span class="facet-value badge rounded-pill bg-secondary float-end">
                            <?php echo $keyword; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-12 col-lg-9">
        <p class="no-modules text-muted mt-5" style="display: none;">No modules found..</p>

        <div class="modules-container modules-container-list">
            <?php foreach ($modules as $idx => $module) : ?>
                <div class="card module mb-2">
                    <div class="card-body">
                        <div class="module-name">
                            <h4 class="card-title mb-0" id="<?php echo $module['name']; ?>">
                                <a href="https://github.com/nf-core/modules/tree/master/<?php echo str_replace("/meta.yml", "", $module['github_path']); ?>" data-bs-toggle="tooltip" title="<?php echo $module['description']; ?>"  class="pipeline-name">
                                    <?php echo $module['name']; ?>
                                    <a href="#<?php echo $module['name']; ?>" class="header-link"><span class=" fas fa-link"></span></a>
                                </a>
                                <button class="btn btn-sm btn-outline-info float-end" data-bs-toggle="collapse" href=".<?php echo $module['name']; ?>-description" aria-expanded="false">
                                    <i class="fas fa-question-circle"></i> Show descriptions
                                </button>
                            </h4>
                        </div>

                        <span class="card-text ms-2 collapse description <?php echo $module['name']; ?>-description">
                            <?php echo $module['description']; ?>
                        </span>
                        <div class="module-params d-flex justify-content-between">
                            <div class="module-input">
                                <label class="text-success">Input</label>

                                <?php
                                $input_text = '<div class="d-flex  flex-wrap">';
                                foreach ($module['input'] as $input) {
                                    foreach ($input as $name => $input_value) {
                                        $description = $input_value['description'];
                                        $description = str_replace('[', '<code class="px-0">[', $description);
                                        $description = str_replace(']', ']</code>', $description);
                                        $input_text .= '<div >';
                                        $input_text .= '<span data-bs-toggle="tooltip" title="'. $input_value['description'] .'">' . $name . ' </span>';
                                        $input_text .= '<span class="text-muted"> (' . $input_value['type'] . ')</span>';
                                        $input_text .= '<p class="text-small collapse mb-1  ms-3 description ' . $module['name'] . '-description" >' .  $description . '</p>';
                                        if (key(end($module['input'])) != $name) { //don't add a comma after the last element
                                            $input_text .= '<span class="hide-collapse">,&nbsp;</span>';
                                        }
                                        $input_text .= '</div>';
                                    }
                                }
                                $input_text .= '</div>';
                                echo $input_text;
                                ?>
                            </div>
                            <div class="module-output">
                                <label class="text-success">Output</label>
                                <?php
                                $output_text = '<div class="d-flex  flex-wrap">';
                                foreach ($module['output'] as $output) {
                                    foreach ($output as $name => $output_value) {
                                        $description = $output_value['description'];
                                        $description = str_replace('[', '<code class="px-0">[', $description);
                                        $description = str_replace(']', ']</code>', $description);
                                        $output_text .= '<div >';
                                        $output_text .= '<span data-bs-toggle="tooltip" title="' . $output_value['description'] . '">' . $name . ' </span>';
                                        $output_text .= '<span class="text-muted"> (' . $output_value['type'] . ')</span>';
                                        $output_text .= '<p class="text-small collapse mb-1 ms-3 description ' . $module['name'] . '-description" >' .  $description . '</p>';
                                        if (key(end($module['output'])) != $name) { //don't add a comma after the last element
                                            $output_text .= '<span class="hide-collapse">,&nbsp;</span>';
                                        }
                                        $output_text .= '</div>';
                                    }
                                }
                                $output_text .= '</div>';
                                echo $output_text;
                                ?>
                            </div>
                            <div class="module-tools">
                                <?php
                                $tool_text = '<div class="d-flex flex-wrap">';
                                foreach ($module['tools'] as $tool) {
                                    // catch incorrectly formatted yamls
                                    if (isset($tool['documentation'])) {
                                        $tmp_tool = [];
                                        $tmp_tool[key($tool)] = array_slice($tool, 1);
                                        $tool = $tmp_tool;
                                    }
                                    foreach ($tool as $name => $tool_value) {
                                        // don't print tools if it has the same name (and therefore usually same description) as the module
                                        if ($module['name'] != $name) {

                                            $tool_text .= '<div>';
                                            $documentation =  $tool_value['documentation'] ? $tool_value['documentation'] : $tool_value['homepage'];
                                            $documentation = '<a class="ms-2" data-bs-toggle="tooltip" title="documentation" href=' . $documentation . '><i class="fas fa-book"></i></a>';
                                            $tool_text .= '<span>' . $name . $documentation . ' </span>';
                                            $description = $tool_value['description'];
                                            $tool_text .= '<span class="text-small collapse description ' . $module['name'] . '-description" >' .  $description . '</span>';
                                            $tool_text .= '</div>';
                                        }
                                        $tool_text .= '</ul>';
                                    }
                                }
                                if (substr_count($tool_text, '<div>') > 0) {
                                    $tool_text = '<label class="text-success">Tools</label>' . $tool_text;
                                }
                                echo $tool_text;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted d-flex align-items-center justify-content-between py-1">
                    <?php if (count($module['keywords']) > 0) : ?>
                        <div class="keywords mb-0">
                            <?php foreach ($module['keywords'] as $keyword) : ?>
                                <a href="/modules?q=<?php echo trim($keyword); ?>" class="badge text-secondary moduletrim(-keyword) px-1"><?php echo $keyword; ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <?php foreach ($module['authors'] as $author) : ?>
                            <img class="float-end contrib-avatars" src="https://github.com/<?php echo trim(str_replace("@", "", $author)); ?>.png">
                        <?php endforeach; ?>
                    </div>
                </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php include('../includes/footer.php'); ?>