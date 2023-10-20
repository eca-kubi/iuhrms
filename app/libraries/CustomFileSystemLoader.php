<?php
use Twig\Loader\FilesystemLoader;
use Twig\Error\LoaderError;
class CustomFileSystemLoader extends FilesystemLoader
{
    /**
     * @throws LoaderError
     */
    public function findTemplate($name, $throw = true): ?string
    {
        $extensions = ['.html.twig', '.php', '.phtml', '.html'];

        foreach ($extensions as $extension) {
            $fullName = $name . $extension;

            try {
                if(file_exists(APP_ROOT . '/views/' . $fullName))
                    return parent::findTemplate($fullName, $throw);
            } catch (LoaderError $e) {
                // Ignore and try next extension
            }
        }
        // No template found
        if ($throw) {
            throw new LoaderError(sprintf('Template "%s" is not defined.', $name));
        }
        return null;
    }
}

