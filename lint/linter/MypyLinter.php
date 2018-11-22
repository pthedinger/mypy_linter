<?php

/**
 * Uses mypy to format python code
 */
final class MypyLinter extends ArcanistExternalLinter {

  public function getInfoName() {
    return 'mypy';
  }

  public function getInfoURI() {
    return '';
  }

  public function getInfoDescription() {
    return pht('Use mypy for processing specified files.');
  }

  public function getLinterName() {
    return 'mypy';
  }

  public function getLinterConfigurationName() {
    return 'mypy';
  }

  public function getLinterConfigurationOptions() {
    $options = array(
    );

    return $options + parent::getLinterConfigurationOptions();
  }

  public function getDefaultBinary() {
    return 'mypy';
  }

  public function getInstallInstructions() {
    return pht('Make sure mypy is in directory specified by $PATH');
  }

  public function shouldExpectCommandErrors() {
    return true;
  }

  protected function getMandatoryFlags() {
    return array(
    );
  }

  protected function getMessageSeverity($code) {
    switch ($code) {
      case "error":
        return ArcanistLintSeverity::SEVERITY_ERROR;
      case "note":
        return ArcanistLintSeverity::SEVERITY_ERROR;
        // return ArcanistLintSeverity::SEVERITY_ADVICE;
      default:
        // This code is unknown, make it an error so that it gets fixed
        return ArcanistLintSeverity::SEVERITY_ERROR;
    }
  }

  protected function failedToParseMessage($path, $line) {
    $message = new ArcanistLintMessage();

    $message->setPath($path)
             ->setChar(1)
             ->setName($this->getLinterName().' failed to parse')
             ->setCode('error')
             ->setDescription("Failed to parse '" . $line . "'")
             ->setSeverity(ArcanistLintSeverity::SEVERITY_ERROR);

    return $message;
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $messages = array();
    if (empty($stdout)) {
      return $messages;
    }

    $root = $this->getProjectRoot();
    $path = Filesystem::resolvePath($path, $root);

    // folder/script.py:3: error: "Class" has no attribute "x"
    $regexp = '/^.*:(?P<line>\d+): (?P<code>.*): (?P<msg>.*)$/';
    $lines = phutil_split_lines($stdout, false);

    $lastLine = '0';

    foreach ($lines as $line) {
        $matches = null;

        if (!preg_match($regexp, $line, $matches)) {
            $message = $this->failedToParseMessage($path, $line);
            $messages[] = $message;
            continue;
        }

        // Create a dictionary of with the contents of the match
        foreach ($matches as $key => $match) {
            $matches[$key] = trim($match);
        }

        if ($matches['line'] == $lastLine and $matches['code'] == 'note') {
            // This is an annotation for the last line - extend the
            // description
            $description = $message->getDescription() . ' ' . $matches['msg'];
            $message->setDescription($description);

        } else {
            $message = new ArcanistLintMessage();
            $messages[] = $message;

            // This is a new error
            $message->setPath($path)
                    ->setLine($matches['line'])
                    ->setChar(1)
                    ->setCode('lint')
                    ->setName($this->getLinterName())
                    ->setDescription($matches['msg'])
                    ->setSeverity($this->getMessageSeverity($matches['code']));
        }

        $lastLine = $matches['line'];
    }
    return $messages;
  }
}
