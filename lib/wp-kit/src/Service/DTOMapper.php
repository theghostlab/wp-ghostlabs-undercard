<?php

namespace GhostLabs\WPKit\Service;

use GhostLabs\WPKit\Contracts\DTOValidationInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

class DTOMapper {
    
    public static function map(array $data, string $dtoClass): object {
        $dto = new $dtoClass();
        $reflection = new ReflectionClass($dtoClass);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (!array_key_exists($propertyName, $data)) {
                continue;
            }

            $value = $data[$propertyName];

            $type = $property->getType();
            if ($type !== null) {
                if ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();
                } elseif ($type instanceof ReflectionUnionType) {
                    $types = $type->getTypes();
                    $typeName = $types[0]->getName();
                } else {
                    $typeName = (string)$type;
                }

                switch ($typeName) {
                    case 'int':
                        $value = (int)$value;
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                    case 'bool':
                        $value = (bool)$value;
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        break;
                    case 'string':
                        $value = (string)$value;
                        break;
                    default:
                        if (enum_exists($typeName)) {
                            $value = self::coerceEnum($value, $typeName);
                        } elseif (class_exists($typeName) && is_array($value)) {
                            $value = self::map($value, $typeName);
                        }
                        break;
                }
            }

            $dto->$propertyName = $value;
        }

        return $dto;
    }

    public static function mapNested(array $data, string $dtoClass, array $nestedMappings): object {
        $dto = self::map($data, $dtoClass);

        foreach ($nestedMappings as $property => $mapping) {
            if (!isset($data[$property]) || !is_array($data[$property])) {
                continue;
            }

            if (is_array($mapping)) {
                $collection = [];
                foreach ($data[$property] as $item) {
                    $collection[] = self::map($item, $mapping[0]);
                }
                $dto->$property = $collection;
            } else {
                $dto->$property = self::map($data[$property], $mapping);
            }
        }

        return $dto;
    }

    public static function validate(object $dto, DTOValidationInterface|array $rules): array {
        if ($rules instanceof DTOValidationInterface) {
            $rules = $rules::rules();
        }
        $errors = [];

        foreach ($rules as $property => $propertyRules) {
            if (!property_exists($dto, $property)) {
                continue;
            }

            $value = $dto->$property;

            if ($value === null && empty($propertyRules['required'])) {
                continue;
            }

            foreach ($propertyRules as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if ($ruleValue && ($value === null || $value === '')) {
                            $errors[$property][] = "$property is required";
                        }
                        break;
                    case 'min':
                        if (is_string($value) && strlen($value) < $ruleValue) {
                            $errors[$property][] = "$property must be at least $ruleValue characters";
                        } elseif (is_numeric($value) && $value < $ruleValue) {
                            $errors[$property][] = "$property must be at least $ruleValue";
                        }
                        break;
                    case 'max':
                        if (is_string($value) && strlen($value) > $ruleValue) {
                            $errors[$property][] = "$property must not exceed $ruleValue characters";
                        } elseif (is_numeric($value) && $value > $ruleValue) {
                            $errors[$property][] = "$property must not exceed $ruleValue";
                        }
                        break;
                    case 'email':
                        if ($ruleValue && $value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$property][] = "$property must be a valid email";
                        }
                        break;
                    case 'regex':
                        if ($value !== null && !preg_match($ruleValue, $value)) {
                            $errors[$property][] = "$property has an invalid format";
                        }
                        break;
                    case 'enum':
                        if ($value !== null && !self::isValidEnumValue($value, $ruleValue)) {
                            $allowed = implode(', ', self::allowedEnumValues($ruleValue));
                            $errors[$property][] = "$property must be one of: $allowed";
                        }
                        break;
                    case 'nested':
                        if (is_object($value)) {
                            $nestedRules = is_string($ruleValue) ? $ruleValue::rules() : $ruleValue;
                            $nestedErrors = self::validate($value, $nestedRules);
                            foreach ($nestedErrors as $nestedProp => $nestedErrs) {
                                $errors["$property.$nestedProp"] = $nestedErrs;
                            }
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    private static function isValidEnumValue(mixed $value, string|array $constraint): bool {
        if (is_array($constraint)) {
            return in_array($value, $constraint, true);
        }

        if (!enum_exists($constraint)) {
            return false;
        }

        return self::coerceEnum($value, $constraint) !== null;
    }

    private static function coerceEnum(mixed $value, string $enumClass): ?\UnitEnum {
        if (!enum_exists($enumClass)) {
            return null;
        }

        if (is_object($value) && $value instanceof $enumClass) {
            return $value;
        }

        if (is_subclass_of($enumClass, \BackedEnum::class)) {
            return (is_int($value) || is_string($value)) ? $enumClass::tryFrom($value) : null;
        }

        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        return null;
    }

    private static function allowedEnumValues(string|array $constraint): array {
        if (is_array($constraint)) {
            return $constraint;
        }

        if (!enum_exists($constraint)) {
            return [];
        }

        return array_map(
            static fn(\UnitEnum $case): string|int => $case instanceof \BackedEnum ? $case->value : $case->name,
            $constraint::cases()
        );
    }

    public static function toArray(object $dto): array {
        $result = [];
        $reflection = new ReflectionClass($dto);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $value = $property->getValue($dto);
            if (is_object($value)) {
                $value = self::toArray($value);
            }
            $result[$property->getName()] = $value;
        }

        return $result;
    }
}
