<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Stmt;
use PHPStan\Parser\Parser;
use PHPStan\Type\StringType;

class StatementResultTest extends \PHPStan\Testing\TestCase
{

	public function dataIsAlwaysTerminating(): array
	{
		return [
			[
				'return;',
				true,
			],
			[
				'foo();',
				false,
			],
			[
				'if (true) { return; }',
				true,
			],
			[
				'if (true) { return; } else { }',
				true,
			],
			[
				'if (false) { } else { return; }',
				true,
			],
			[
				'if (false) { } else { }',
				false,
			],
			[
				'if (doFoo()) { return; } else { }',
				false,
			],
			[
				'if (doFoo()) { return; } else { return; }',
				true,
			],
			[
				'if (doFoo()) { continue; } else { break; }',
				true,
			],
			[
				'if (doFoo()) { continue; }',
				false,
			],
			[
				'if (doFoo()) { continue; } else { continue; }',
				true,
			],
			[
				'if (doFoo()) { if (true) { return; } } else { }',
				false,
			],
			[
				'if (doFoo()) { if (true) { return; } } else { return; }',
				true,
			],
			[
				'foreach ([1, 2, 3] as $v) { return; }',
				true,
			],
			[
				'foreach ([1, 2, 3] as $v) { break; }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $v) { continue; }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $v) { if (doFoo()) { return; } }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $v) { if (doFoo()) { return; } else { return; } }',
				true,
			],
			[
				'foreach ([1, 2, 3] as $v) { if (true) { return; } }',
				true,
			],
			[
				'foreach ([1, 2, 3] as $v) { if (true) { break; } }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $v) { if (true) { continue; } }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $v) { if (doFoo()) { return; } else { break; } }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $v) { if (doFoo()) { if (doBar()) { return; } else { break; } } else { break; } }',
				false,
			],
			[
				'switch ($x) { case 1: return; default: return; }',
				true,
			],
			[
				'switch ($x) { case 1: return; }',
				false,
			],
			[
				'switch ($x) { case 1: if (doFoo()) return; default: return; }',
				true,
			],
			[
				'switch ($x) { case 1: break; default: return; }',
				false,
			],
			[
				'switch ($x) { case 1: if (doFoo()) { break; } else { break; }; default: return; }',
				false,
			],
			[
				'switch ($x) { case 1: if (doFoo()) { return; } else { break; }; default: return; }',
				false,
			],
			[
				'try { return; } finally { }',
				true,
			],
			[
				'try { } finally { return; }',
				true,
			],
			[
				'try { return; } catch (Exception $e) { return; }',
				true,
			],
			[
				'try { return; } catch (Exception $e) { }',
				false,
			],
			[
				'try { break; } catch (Exception $e) { break; }',
				true,
			],
			[
				'try { break; } catch (Exception $e) { break; } catch (OtherException $e) { return; }',
				true,
			],
			[
				'try { break; } catch (Exception $e) { break; } catch (OtherException $e) { }',
				false,
			],
			[
				'while (true) { }',
				true,
			],
			[
				'while (true) { return; }',
				true,
			],
			[
				'while (true) { break; }',
				false,
			],
			[
				'do { } while (doFoo());',
				false,
			],
			[
				'do { return; } while (doFoo());',
				true,
			],
			[
				'do { break; } while (doFoo());',
				false,
			],
			[
				'for ($i = 0; $i < 5; $i++) { }',
				false,
			],
			[
				'for ($i = 0; $i < 5; $i++) { break; }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $val) { if ($val === 1) { continue; } else { throw new \Exception(); } }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $val) { if ($val === 1) { continue; } throw new \Exception(); }',
				false,
			],
			[
				'{ if ($val === 1) { continue; } throw new \Exception(); }',
				true,
			],
			[
				'throw new \Exception();',
				true,
			],
			[
				'foreach ([] as $val) { return; }',
				false,
			],
			[
				'foreach ($arr as $val) { return; }',
				false,
			],
			[
				'foreach ([1, 2, 3] as $val) { return; }',
				true,
			],
			[
				'while (true) { return; }',
				true,
			],
			[
				'while ($bool) { return; }',
				false,
			],
			[
				'for ($i = 0; $i < 10; $i++) { return; }',
				false, // will be true with range types
			],
			[
				'for ($i = 0; $i < 0; $i++) { return; }',
				false,
			],
			[
				'for ($i = 0; $i < count($arr); $i++) { return; }',
				false,
			],
			[
				'do { return; } while (true);',
				true,
			],
			[
				'do { return; } while (false);',
				true,
			],
			[
				'do { return; } while ($maybe);',
				true,
			],
			[
				'switch ($cond) { case 1: case 2: return; default: return; }',
				true,
			],
			[
				'switch ($cond) { case 1: case 2: return; }',
				false,
			],
			[
				'switch ($cond) { case 1: break; case 2: return; }',
				false,
			],
			[
				'switch ($cond) { case 1: break; case 2: return; default: return; }',
				false,
			],
			[
				'switch ($i) { case 0: return 1; case 1: case 2: default: }',
				false,
			],
			[
				'while (true) { break; }',
				false,
			],
			[
				'while (true) { continue; }',
				true,
			],
			[
				'if (doFoo()) { continue; } else { return; }',
				true,
			],
			[
				'while (true) { if (doFoo()) { continue; } else { return; } }',
				true,
			],
			[
				'while (true) { if (doFoo()) { continue; } elseif (doBar()) { doBaz(); } else { return; } }',
				true,
			],
			[
				'while (true) { if (doFoo()) { break; } elseif (doBar()) { doBaz(); } else { return; } }',
				false,
			],
			[
				'while (true) { if (doFoo()) { break; } else { return; } }',
				false,
			],
			[
				'while (true) { if (doFoo()) { continue; } else { throw new \Exception(); } }',
				true,
			],
			[
				'while (doFoo()) { if (doFoo()) { continue; } else { return; } }',
				false,
			],
			[
				'do { break; } while (true);',
				false,
			],
			[
				'do { continue; } while (true);',
				false,
			],
			[
				'do { if (doFoo()) { continue; } else { return; } } while (true);',
				false,
			],
			[
				'do { if (doFoo()) { continue; } else { return; } } while (doFoo());',
				false,
			],
			[
				'while (true) { try { return true; } catch (\Exception $e) { doFoo(); } }',
				true,
			],
			[
				'while ($string !== null) { $string = null; try { return true; } catch (\Exception $e) { doFoo(); } }',
				false,
			],
			[
				'while ($string !== null) { $string = null; try { return true; } catch (\Exception $e) { doFoo(); } }',
				false,
			],
			[
				'try { return true; } catch (\Exception $e) { doFoo(); }',
				false,
			],
			[
				'do { try { return true; } catch (\Exception $e) { doFoo(); } } while (true);',
				false,
			],
			[
				'while ($string !== null) { $string = rand(0, 1) ? $string : null; if ($string !== null) { return; } else { continue; } }',
				false,
			],
			[
				'while (true) { $string = rand(0, 1) ? $string : null; if ($string !== null) { return; } else { continue; } }',
				true,
			],
			[
				'while ($string !== null) { $string = null; return; }',
				true,
			],
		];
	}

	/**
	 * @dataProvider dataIsAlwaysTerminating
	 * @param string $code
	 * @param bool $expectedIsAlwaysTerminating
	 */
	public function testIsAlwaysTerminating(
		string $code,
		bool $expectedIsAlwaysTerminating
	): void
	{
		/** @var Parser $parser */
		$parser = self::getContainer()->getByType(Parser::class);

		/** @var Stmt[] $stmts */
		$stmts = $parser->parseString(sprintf('<?php %s', $code));

		/** @var NodeScopeResolver $nodeScopeResolver */
		$nodeScopeResolver = self::getContainer()->getByType(NodeScopeResolver::class);
		/** @var ScopeFactory $scopeFactory */
		$scopeFactory = self::getContainer()->getByType(ScopeFactory::class);
		$scope = $scopeFactory->create(ScopeContext::create('test.php'))
			->assignVariable('string', new StringType());
		$result = $nodeScopeResolver->processStmtNodes(
			new Stmt\Namespace_(null, $stmts),
			$stmts,
			$scope,
			static function (): void {
			}
		);
		$this->assertSame($expectedIsAlwaysTerminating, $result->isAlwaysTerminating());
	}

}
