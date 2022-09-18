<?php

//APPLICATION ENUMS

enum LanguagesEnum
{
    case C;
    case CPP;
    case JAVA;
    case PYTHON;
    case JAVASCRIPT;
    case PHP;

    public static function getObject(string $name): LanguagesEnum | null
    {
        return match ($name) {
            "C" => LanguagesEnum::C,
            "CPP" => LanguagesEnum::CPP,
            "JAVA" => LanguagesEnum::JAVA,
            "PYTHON" => LanguagesEnum::PYTHON,
            "JAVASCRIPT" => LanguagesEnum::JAVASCRIPT,
            "PHP" => LanguagesEnum::PHP,
            default => null
        };
    }

    public function getCommand(): array
    {
        return match ($this) {
            LanguagesEnum::C => ["chdir", "g++ -o {{FILE_UPLOAD_PATH}}{{FILE_NAME}}.exe {{FILE_PATH}} 2>&1", "{{FILE_UPLOAD_PATH}}{{FILE_NAME}}.exe 2>&1"],
            LanguagesEnum::CPP => ["chdir", "g++ -o {{FILE_UPLOAD_PATH}}{{FILE_NAME}}.exe {{FILE_PATH}} 2>&1", "{{FILE_UPLOAD_PATH}}{{FILE_NAME}}.exe 2>&1"],
            LanguagesEnum::JAVA => ["chdir", "javac {{FILE_PATH}} 2>&1", "java {{FILE_NAME}} 2>&1"],
            LanguagesEnum::PYTHON => ["chdir", "C:\Users\mohit\AppData\Local\Programs\Python\Python39\python.exe {{FILE_PATH}} 2>&1"],
            LanguagesEnum::JAVASCRIPT => ["chdir", "node {{FILE_PATH}} 2>&1"],
            LanguagesEnum::PHP => ["chdir", "php {{FILE_PATH}} 2>&1"],
            default => [""]
        };
    }
    
    public function getCodeFileUploadPath(): string
    {
        return match ($this) {
            LanguagesEnum::C => CODE_FILE_UPLOAD_DIRECTORY . "c/",
            LanguagesEnum::CPP => CODE_FILE_UPLOAD_DIRECTORY . "cpp/",
            LanguagesEnum::JAVA => CODE_FILE_UPLOAD_DIRECTORY . "java/",
            LanguagesEnum::PYTHON => CODE_FILE_UPLOAD_DIRECTORY . "python/",
            LanguagesEnum::JAVASCRIPT => CODE_FILE_UPLOAD_DIRECTORY . "node/",
            LanguagesEnum::PHP => CODE_FILE_UPLOAD_DIRECTORY . "php/",
            default => ""
        };
    }

    public function getFileExtension(): string
    {
        return match ($this) {
            LanguagesEnum::C => ".c",
            LanguagesEnum::CPP => ".cpp",
            LanguagesEnum::JAVA => ".java",
            LanguagesEnum::PYTHON => ".py",
            LanguagesEnum::JAVASCRIPT => ".js",
            LanguagesEnum::PHP => ".php",
            default => ""
        };
    }

}