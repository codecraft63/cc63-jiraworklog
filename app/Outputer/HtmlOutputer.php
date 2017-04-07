<?php
/**
 * Created by PhpStorm.
 * User: pedro
 * Date: 06/04/17
 * Time: 23:49
 */

namespace App\Outputer;


class HtmlOutputer extends BaseOutputer implements OutputerInterface
{
    public function output()
    {
        $filename = getenv('TEMPLATE_DIR') . '/' . $this->options['template'] . '.html.php';
        $output_dir = $this->options['output-dir'] . '/output.html';

        $data = $this->data;
        ob_start();
        require $filename;
        $output = ob_get_clean();

        file_put_contents($output_dir, $output);
    }
}