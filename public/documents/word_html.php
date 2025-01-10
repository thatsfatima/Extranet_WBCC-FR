<?php

class Word_html
{
    protected $fileName;
    protected $style = array();

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        header("Content-type: application/vnd.ms-word");  
        header("Content-Disposition: attachment;Filename=".$fileName);
        header("Pragma: no-cache");  
        header("Expires: 0");
    }

    public function render($html)
    {
        echo $html;
    }

    public function AddPage()
    {
        $this->Footer();
        echo "</section>";
        $this->Header();
        echo "<div class='page-break'><br></div>";
        echo "<section style = 'margin: 10% 10% 10% 10%; padding: 10% 10% 10% 10%; width: 100%; height: 80%; display: flex; justify-content: center; align-items: center; overflow: auto; position: relative;'>";
    }

    public function Header()
    {
        echo"";
    }

    public function Footer()
    {
        // echo"<div style = 'margin: 10% 10% 10% 10%; padding: 10% 10% 10% 10%; width: 100%; height: 5%; display: flex; flex-direction: row; justify-content: center; align-items: center; overflow: auto;'></div>";
    }

    public function AddStyle($class, $style)
    {
        $this->style[$class] = $style;
    }

    public function startOfDoc()
    {
        echo "<!DOCTYPE html><html lang='fr'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><meta name='author' content='Nom de l'Auteur'><style>.page-break {page-break-before: always; display: block;}body{width: 100%; font-family: Arial; margin: 10% 10% 10% 10%; padding: 10% 10% 10% 10%;}";
        if (!empty($this->style)) {
            foreach ($this->style as $key => $value) {
                echo "$key {$value}";
            }
        }
        echo "</style></head><body>";
        echo "<section style = 'margin: 10% 10% 10% 10%; padding: 10% 10% 10% 10%; width: 100%; height: 80%; display: flex; justify-content: center; align-items: center; overflow: auto; position: relative;'>";
    }

    public function endOfDoc()
    {
        echo "<script type='application/ld+json'>
{
  '@context': 'https://schema.org',
  '@type': 'WebPage',
  'author': {
    '@type': 'Person',
    'name': 'WBCC'
  }
}
</script>
</body></html>";
    }
}
?>